<?php

if (!function_exists('datatablesSearch')) {
    function datatablesSearch($query, $searchable = [])
    {
        // Apply server-side searching
        if (request()->has('search') && !empty(request()->input('search')['value'])) {
            $searchValue = request()->input('search')['value'];
            foreach ($searchable as $key => $column) {
                if ($key == 0) {
                    $query->where($column, 'like', '%' . $searchValue . '%');
                } 
                else {
                    $query->orWhere($column, 'like', '%' . $searchValue . '%');
                }
            }
            // Add other conditions for searching if needed
        }
    }
}

if (!function_exists('datatablesSearchWithRelasion')) {
    function datatablesSearchWithRelasion($query, $searchable = [])
    {
        if (request()->has('search') && !empty(request()->input('search')['value'])) {
            $searchValue = request()->input('search')['value'];
            $mainTable = $query->getModel()->getTable();

            $query->where(function ($q) use ($searchable, $searchValue, $mainTable) {
                foreach ($searchable as $column) {
                    if (strpos($column, '.') !== false) {
                        // Handle nested relations (e.g., subdistrict.subdistrict.province.name)
                        $parts = explode('.', $column);
                        $columnName = array_pop($parts);
                        $relations = $parts;

                        $q->orWhere(function ($subQuery) use ($relations, $columnName, $searchValue) {
                            applyNestedWhereHas($subQuery, $relations, $columnName, $searchValue);
                        });
                    } else {
                        $q->orWhere("{$mainTable}.{$column}", 'like', "%{$searchValue}%");
                    }
                }
            });
        }
    }
}

if (!function_exists('applyNestedWhereHas')) {
    function applyNestedWhereHas($query, $relations, $columnName, $searchValue)
    {
        $relation = array_shift($relations); // Ambil relasi pertama

        $query->whereHas($relation, function ($q) use ($relations, $columnName, $searchValue) {
            if (!empty($relations)) {
                // Rekursif untuk relasi berikutnya
                applyNestedWhereHas($q, $relations, $columnName, $searchValue);
            } else {
                // Jika sudah di relasi terakhir, tambahkan where
                $q->where($columnName, 'like', "%{$searchValue}%");
            }
        });
    }
}

if (!function_exists('datatablesOrder')) {
    function datatablesOrder($query, $columnNames)
    {
        // Get the column to be sorted and its direction from the request
        $sortColumnIndex = request()->input('order.0.column', 0);
        $sortDirection = request()->input('order.0.dir', 'desc');

        // Get the actual column name to use for sorting
        $sortColumn = $columnNames[$sortColumnIndex];
        $query->orderBy($sortColumn, $sortDirection);
    }
}

if (!function_exists('datatablesOrderRelasion')) {
    function datatablesOrderRelasion($query, $columnNames)
    {
        $sortColumnIndex = request()->input('order.0.column', 0);
        $sortDirection = request()->input('order.0.dir', 'desc');
        $sortColumn = $columnNames[$sortColumnIndex];

        $mainTable = $query->getModel()->getTable(); // Contoh: 'users'

        // Jika kolom pengurutan adalah relasi (contoh: district.name)
        if (strpos($sortColumn, '.') !== false) {
            $parts = explode('.', $sortColumn);
            $column = array_pop($parts); // Kolom tujuan (name)
            $relations = $parts; // Array relasi (district)

            $currentTable = $mainTable;

            // Join tabel relasi dengan alias unik
            foreach ($relations as $relation) {
                $relatedModel = $query->getModel()->{$relation}()->getRelated();
                $relatedTable = $relatedModel->getTable();
                $alias = "{$relatedTable}_alias"; // Contoh: districts_alias
                $foreignKey = $query->getModel()->{$relation}()->getForeignKeyName();

                $query->join(
                    "{$relatedTable} as {$alias}",
                    "{$currentTable}.{$foreignKey}",
                    '=',
                    "{$alias}.id"
                );

                $currentTable = $alias;
                $query->setModel($relatedModel); // Update model ke relasi
            }

            $sortColumn = "{$currentTable}.{$column}";
        } else {
            // Ambil dari tabel utama (users.created_at)
            $sortColumn = "{$mainTable}.{$sortColumn}";
        }

        $query->orderBy($sortColumn, $sortDirection);
    }
}

if (!function_exists('datatablesPagination')) {
    function datatablesPagination($query)
    {
        // Apply pagination based on the Datatable's request
        $start = request()->input('start', 0);
        $length = request()->input('length', 10);
        $query->offset($start)->limit($length);
    }
}


if (!function_exists('datatablesJsonSearchWithRelasion')) {
    function datatablesJsonSearchWithRelasion($data, $totalRecords, $filteredRecords)
    {
        return response()->json([
            "draw" => intval(request()->input('draw')),
            "recordsTotal" => $totalRecords, // **Total semua data tanpa filter**
            "recordsFiltered" => $filteredRecords, // **Total setelah filter/search diterapkan**
            "data" => $data
        ]);
    }
}

if (!function_exists('datatablesJson')) {
    function datatablesJson($data, $totalRecords)
    {
        return response()->json([
            'data' => $data,           // The actual data for the table
            'recordsTotal' => $totalRecords,    // Total number of records without filtering
            'recordsFiltered' => $totalRecords, // Total number of records after filtering (if implemented)
            'draw' => request()->input('draw'),
        ]);
    }
}

if (!function_exists('datatablesActionButton')) {
    function datatablesActionButton($data, $columnNames, $actionButtons = [], $bootstrap = null)
    {
        $data->transform(function ($item) use($columnNames, $actionButtons, $bootstrap) {
            // You can customize the URL and icons as needed
            foreach ($columnNames as $column) {
                if (strpos($column, "_id")) {
                    $item->{$column} = $item->{str_replace('_id', '', $column)}->name;
                }
                if (strpos($column, " as ")) {
                    $arrColumn = explode(' ', $column);
                    $arrTableColumn = explode('.', reset($arrColumn));
                    $item->{end($arrColumn)} = $item->{reset($arrTableColumn)}[0]->{end($arrTableColumn)} ?? '';
                }
            }
            $idRow = isset($item->slug)?$item->slug:$item->id;
            if ($bootstrap == 5) {
                $bootstrap5 = '<div class="btn-group mx-auto">';
                $bootstrap5 .= '<button class="btn btn-soft-primary btn-sm dropdown-toggle"  id="dropdownAction" data-bs-toggle="dropdown" aria-expanded="false">';
                $bootstrap5 .= 'Action';
                $bootstrap5 .= '</button>';
                $bootstrap5 .= '<div class="dropdown-menu" aria-labelledby="dropdownAction">';
                foreach ($actionButtons as $button) {
                    if ($button['id']) {
                        if (strstr($button['route'], 'destroy')) {
                            $bootstrap5 .= '<a href="#" class="dropdown-item" onclick="event.preventDefault(); if (confirm(\'Are you sure you want to delete this data?\')) { document.getElementById(\'delete-form-'.$idRow.'\').submit(); }">'.$button['name'].'</a>';
                            $bootstrap5 .= '<form id="delete-form-'.$idRow.'" action="'.route($button['route'], $idRow).'" method="POST" style="display: none;">';
                            $bootstrap5 .= '<input type="hidden" name="_method" value="DELETE">';
                            $bootstrap5 .= '<input type="hidden" name="_token" value="'.csrf_token().'">';
                            $bootstrap5 .= '</form>';
                        } else {
                            $bootstrap5 .= '<a href="'.route($button['route'], $idRow).'" class="dropdown-item">'.$button['name'].'</a> ';
                        }
                    } else {
                        $bootstrap5 .= '<a href="'.route($button['route']).'" class="dropdown-item">'.$button['name'].'</a> ';
                    }
                }
                $bootstrap5 .= '</div>';
                $bootstrap5 .= '</div>';
                $action = $bootstrap5;
            } else {
                $bootstrap4 = '<div class="btn-group mx-auto">';
                $bootstrap4 .= '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                $bootstrap4 .= 'Action';
                $bootstrap4 .= '</button>';
                $bootstrap4 .= '<div class="dropdown-menu">';
                foreach ($actionButtons as $button) {
                    if ($button['id']) {
                        if (strstr($button['route'], 'destroy')) {
                            $bootstrap4 .= '<a href="#" class="dropdown-item" onclick="event.preventDefault(); if (confirm(\'Are you sure you want to delete this data?\')) { document.getElementById(\'delete-form-'.$idRow.'\').submit(); }">'.$button['name'].'</a>';
                            $bootstrap4 .= '<form id="delete-form-'.$idRow.'" action="'.route($button['route'], $idRow).'" method="POST" style="display: none;">';
                            $bootstrap4 .= '<input type="hidden" name="_method" value="DELETE">';
                            $bootstrap4 .= '<input type="hidden" name="_token" value="'.csrf_token().'">';
                            $bootstrap4 .= '</form>';
                        } else {
                            $bootstrap4 .= '<a href="'.route($button['route'], $idRow).'" class="dropdown-item">'.$button['name'].'</a> ';
                        }
                    } else {
                        $bootstrap4 .= '<a href="'.route($button['route']).'" class="dropdown-item">'.$button['name'].'</a> ';
                    }
                }
                $bootstrap4 .= '</div>';
                $bootstrap4 .= '</div>';
                $action = $bootstrap4;
            }

            // Add the actions column to the row
            $item->action = $action;

            return $item;
        });
    }
}

if (!function_exists('datatablesFormater')) {
    function datatablesFormater($query, $columnNames, $actionButtons = null, $searchable = null, $bootstrap = null)
    {
        // Get the total number of records before applying pagination
        $totalRecords = $query->count();

        // Apply ordering
        datatablesOrder($query, $columnNames);

        // Apply server-side searching
        datatablesSearch($query, $searchable);

        // Apply pagination based on the Datatable's request
        datatablesPagination($query);

        // Fetch the records
        $data = $query->get();

        //Add the action buttons to each row
        datatablesActionButton($data, $columnNames, $actionButtons, $bootstrap);

        return datatablesJson($data, $totalRecords);
    }
}

if (!function_exists('datatablesFormaterWithSearchRelasion')) {

    function datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons = null, $searchable = null, $bootstrap = null)
    {
        // Get the total number of records before applying pagination
        $totalRecords = $query->count();

        // Apply ordering
        datatablesOrderRelasion($query, $columnNames);

        // Apply server-side searching
        datatablesSearchWithRelasion($query, $searchable);

        $filteredRecords = $query->count();
        
        // Apply pagination based on the Datatable's request
        datatablesPagination($query);

        // Fetch the records
        $data = $query->get();

        //Add the action buttons to each row
        datatablesActionButton($data, $columnNames, $actionButtons, $bootstrap);

        return datatablesJsonSearchWithRelasion($data, $totalRecords, $filteredRecords);
    }
}
