<?php

namespace App\Services\Olt;

use App\Models\Olt;
use App\Services\Olt\Contracts\SnmpClient;
use App\Services\Olt\Exceptions\SnmpException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class NetSnmpClient implements SnmpClient
{
    public function get(Olt $olt, string $oid): ?string
    {
        $this->assertNumericOid($oid);

        if ($this->nativeExtensionAvailable($olt)) {
            $value = $this->nativeGet($olt, $oid);

            if ($value === false) {
                throw new SnmpException('OLT tidak memberikan respons SNMP.');
            }

            return $this->cleanValue((string) $value);
        }

        $arguments = array_merge(
            [$this->binary('snmpget'), '-Oqv', '-Ot'],
            $this->connectionArguments($olt),
            [$this->target($olt), $oid]
        );

        $output = $this->run($arguments);

        return $this->cleanValue(trim($output));
    }

    public function walk(Olt $olt, string $oid): array
    {
        $this->assertNumericOid($oid);

        if ($this->nativeExtensionAvailable($olt)) {
            $values = $this->nativeWalk($olt, $oid);

            if ($values === false) {
                throw new SnmpException('SNMP walk gagal atau tidak didukung OLT.');
            }

            $result = [];
            foreach ($values as $fullOid => $value) {
                $result[$this->oidSuffix($oid, (string) $fullOid)] = $this->cleanValue((string) $value);
            }

            return $result;
        }

        $arguments = array_merge(
            [$this->binary('snmpwalk'), '-On'],
            $this->connectionArguments($olt),
            [$this->target($olt), $oid]
        );

        $result = [];
        foreach (preg_split('/\R/', trim($this->run($arguments))) ?: [] as $line) {
            if (!preg_match('/^(\.?[0-9.]+)\s*=\s*(.*)$/', trim($line), $matches)) {
                continue;
            }

            $result[$this->oidSuffix($oid, $matches[1])] = $this->cleanValue($matches[2]);
        }

        return $result;
    }

    private function nativeExtensionAvailable(Olt $olt): bool
    {
        return match ($olt->snmp_version) {
            '1' => function_exists('snmpget') && function_exists('snmprealwalk'),
            '3' => function_exists('snmp3_get') && function_exists('snmp3_real_walk'),
            default => function_exists('snmp2_get') && function_exists('snmp2_real_walk'),
        };
    }

    private function nativeGet(Olt $olt, string $oid)
    {
        $timeout = max(1, (int) config('olt.timeout_seconds', 3)) * 1_000_000;
        $retries = max(0, (int) config('olt.retries', 1));

        return match ($olt->snmp_version) {
            '1' => @snmpget($this->target($olt), (string) $olt->snmp_community, $oid, $timeout, $retries),
            '3' => @snmp3_get(
                $this->target($olt),
                (string) $olt->snmp_username,
                (string) $olt->snmp_security_level,
                (string) $olt->snmp_auth_protocol,
                (string) $olt->snmp_auth_password,
                (string) $olt->snmp_priv_protocol,
                (string) $olt->snmp_priv_password,
                $oid,
                $timeout,
                $retries
            ),
            default => @snmp2_get($this->target($olt), (string) $olt->snmp_community, $oid, $timeout, $retries),
        };
    }

    private function nativeWalk(Olt $olt, string $oid)
    {
        $timeout = max(1, (int) config('olt.timeout_seconds', 3)) * 1_000_000;
        $retries = max(0, (int) config('olt.retries', 1));

        return match ($olt->snmp_version) {
            '1' => @snmprealwalk($this->target($olt), (string) $olt->snmp_community, $oid, $timeout, $retries),
            '3' => @snmp3_real_walk(
                $this->target($olt),
                (string) $olt->snmp_username,
                (string) $olt->snmp_security_level,
                (string) $olt->snmp_auth_protocol,
                (string) $olt->snmp_auth_password,
                (string) $olt->snmp_priv_protocol,
                (string) $olt->snmp_priv_password,
                $oid,
                $timeout,
                $retries
            ),
            default => @snmp2_real_walk($this->target($olt), (string) $olt->snmp_community, $oid, $timeout, $retries),
        };
    }

    private function connectionArguments(Olt $olt): array
    {
        $arguments = [
            '-v'.$olt->snmp_version,
            '-t', (string) max(1, (int) config('olt.timeout_seconds', 3)),
            '-r', (string) max(0, (int) config('olt.retries', 1)),
        ];

        if ($olt->snmp_version !== '3') {
            if (!$olt->snmp_community) {
                throw new SnmpException('SNMP community belum diisi.');
            }

            return array_merge($arguments, ['-c', (string) $olt->snmp_community]);
        }

        if (!$olt->snmp_username) {
            throw new SnmpException('Username SNMPv3 belum diisi.');
        }

        $arguments = array_merge($arguments, [
            '-l', (string) $olt->snmp_security_level,
            '-u', (string) $olt->snmp_username,
        ]);

        if (in_array($olt->snmp_security_level, ['authNoPriv', 'authPriv'], true)) {
            $arguments = array_merge($arguments, [
                '-a', (string) ($olt->snmp_auth_protocol ?: 'SHA'),
                '-A', (string) $olt->snmp_auth_password,
            ]);
        }

        if ($olt->snmp_security_level === 'authPriv') {
            $arguments = array_merge($arguments, [
                '-x', (string) ($olt->snmp_priv_protocol ?: 'AES'),
                '-X', (string) $olt->snmp_priv_password,
            ]);
        }

        return $arguments;
    }

    private function run(array $arguments): string
    {
        $process = new Process($arguments);
        $process->setTimeout(
            (max(1, (int) config('olt.timeout_seconds', 3)) * (max(0, (int) config('olt.retries', 1)) + 1)) + 2
        );

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $message = trim($process->getErrorOutput()) ?: trim($process->getOutput());
            $message = preg_replace('/\s+/', ' ', $message) ?: 'Tidak ada respons dari OLT.';

            throw new SnmpException('SNMP gagal: '.mb_substr($message, 0, 300), 0, $exception);
        }

        return $process->getOutput();
    }

    private function target(Olt $olt): string
    {
        return $olt->host.':'.$olt->port;
    }

    private function binary(string $name): string
    {
        $path = (string) config("olt.{$name}_binary");

        if ($path === '' || !is_executable($path)) {
            throw new SnmpException(
                "Ekstensi PHP SNMP tidak tersedia dan binary {$name} tidak ditemukan."
            );
        }

        return $path;
    }

    private function assertNumericOid(string $oid): void
    {
        if (!preg_match('/^\.?[0-9]+(?:\.[0-9]+)*$/', $oid)) {
            throw new SnmpException("OID tidak valid: {$oid}");
        }
    }

    private function oidSuffix(string $baseOid, string $fullOid): string
    {
        $base = ltrim($baseOid, '.');
        $full = ltrim($fullOid, '.');

        return ltrim(str_starts_with($full, $base) ? substr($full, strlen($base)) : $full, '.');
    }

    private function cleanValue(string $value): string
    {
        $value = trim($value);
        $value = preg_replace(
            '/^(?:STRING|INTEGER|OID|Hex-STRING|Timeticks|Counter32|Counter64|Gauge32|IpAddress):\s*/i',
            '',
            $value
        ) ?? $value;

        if (strlen($value) >= 2 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            $value = stripcslashes(substr($value, 1, -1));
        }

        return trim($value);
    }
}
