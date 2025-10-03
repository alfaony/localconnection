<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'supplier_category_id',
        'supplier_type_id',
        'item_name',
        'description',
        'estimated_price',
        'assigned_pic_id',
        'status',
        'qty',
        'picture',
        'is_open',
        'close_reason',
    ];

    protected $appends = ['status_badge','price_with_format','status_open','action_permission'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function assignedPic()
    {
        return $this->belongsTo(User::class, 'assigned_pic_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(SupplierCategory::class,'supplier_category_id');
    }


    public function potentialVendors()
    {
        return $this->hasMany(PotentialVendor::class);
    }

    public function purchase()
    {
        return $this->hasMany(ItemPurchase::class, 'item_request_id');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'item_request_id');
    }

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function getStatusBadgeAttribute()
    {
        $status = strtoupper($this->status); // pastikan uppercase
        switch ($status) {
            case 'REQUESTED':
                return '<span class="badge badge-secondary"><i class="fas fa-paper-plane mr-1"></i> Requested</span>';
            case 'LOOKING_FOR_SPRINTER':
                return '<span class="badge badge-info"><i class="fas fa-bicycle mr-1"></i> Looking for Sprinter</span>';
            case 'LOOKING_FOR_ITEM':
                return '<span class="badge badge-warning"><i class="fas fa-search mr-1"></i> Looking for Item</span>';
            case 'WAITING_PAYMENT':
                return '<span class="badge badge-primary"><i class="fas fa-credit-card mr-1"></i> Waiting Payment</span>';
            case 'WAITING_DELIVERY_CONFIRMATION':
                return '<span class="badge badge-success"><i class="fas fa-truck-loading mr-1"></i> Waiting Delivery Confirmation</span>';
            case 'DELIVERED':
                return '<span class="badge badge-success"><i class="fas fa-truck mr-1"></i> Delivered</span>';
            case 'CLOSED':
                return '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i> Closed</span>';

            default:
                return '<span class="badge badge-dark"><i class="fas fa-question-circle mr-1"></i>'.$status.'</span>';
        }
    }

    public function getActionPermissionAttribute()
    {
        $status = strtoupper($this->status); // pastikan uppercase
        if (in_array($status, ['WAITING_PAYMENT', 'WAITING_DELIVERY_CONFIRMATION', 'DELIVERED', 'CLOSED'])) 
        {
            return false;
        }
        return true;
    }

    public function getPriceWithFormatAttribute()
    {
        return 'Rp ' . number_format($this->estimated_price, 0, ',', '.');
    }

    
    public function getIsCompletePaymentAttribute(): bool
    {
        // Ambil semua itemPurchase
        $purchases = $this->purchase;

        // Jika tidak ada purchase, dianggap belum complete
        if ($purchases->isEmpty()) {
            return false;
        }

        // Jika semua purchase punya payment → true
        return $purchases->every(function ($purchase) {
            return $purchase->payment !== null;
        });
    }

    public function getStatusOpenAttribute()
    {
        $badgeClass = $this->is_open ? 'badge-success' : 'badge-danger';
        $statusText = $this->is_open ? 'Open' : 'Closed';

        return "<span class=\"badge {$badgeClass} rounded-pill\">{$statusText}</span>";
    }
}
