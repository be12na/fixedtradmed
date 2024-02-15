<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute harus diterima.',
    'accepted_if' => ':attribute harus diterima ketika :other adalah :value.',
    'active_url' => ':attribute bukan URL aktif.',
    'after' => ':attribute harus tanggal setelah :date.',
    'after_or_equal' => ':attribute harus tanggal setelah atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, - dan _.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute harus berisikan daftar list.',
    'before' => ':attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':attribute harus berisi antara :min and :max item.',
        'file' => ':attribute harus berisi antara :min and :max kb.',
        'numeric' => ':attribute harus berisi antara :min and :max.',
        'string' => ':attribute harus berisi antara :min and :max karakter.',
    ],
    'boolean' => ':attribute harus berisi ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak sama.',
    'current_password' => 'Password salah.',
    'date' => ':attribute bukan tanggal yang benar.',
    'date_equals' => ':attribute harus sama dengan :date.',
    'date_format' => 'Format :attribute tidak sesuai dengan format :format.',
    'declined' => ':attribute harus ditolak.',
    'declined_if' => ':attribute harus ditolak ketika :other adalah :value.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus terdiri dari :digits digit.',
    'digits_between' => ':attribute harus berisi antara :min dan :max digit.',
    'dimensions' => 'Dimensi :attribute tidak benar.',
    'distinct' => ':attribute memiliki isian yang sama.',
    'email' => ':attribute harus berupa alamat email yang benar.',
    'ends_with' => ':attribute harus berakhiran: :values.',
    'enum' => ':attribute yang dipilih tidak benar.',
    'exists' => 'Pilihan :attribute tidak tersedia.',
    'file' => ':attribute harus berupa file.',
    'filled' => ':attribute harus memiliki isian yang benar.',
    'gt' => [
        'array' => ':attribute harus lebih dari :value item.',
        'file' => ':attribute harus lebih besar dari :value kb.',
        'numeric' => ':attribute harus lebih besar dari :value.',
        'string' => ':attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => ':attribute harus memiliki :value item atau lebih.',
        'file' => ':attribute harus lebih besar atau sama dengan :value kb.',
        'numeric' => ':attribute harus lebih besar atau sama dengan :value.',
        'string' => ':attribute harus lebih besar atau sama dengan :value karakter.',
    ],
    'image' => ':attribute harus berupa file gambar.',
    'in' => 'Pilihan :attribute tidak tersedia.',
    'in_array' => ':attribute tidak tersedia di :other.',
    'integer' => ':attribute harus berupa angka bilangan bulat.',
    'ip' => ':attribute harus berupa alamat IP yang benar.',
    'ipv4' => ':attribute harus berupa alamat IPv4 yang benar.',
    'ipv6' => ':attribute harus berupa alamat IPv6 yang benar.',
    'json' => ':attribute harus berupa JSON string.',
    'lt' => [
        'array' => ':attribute harus kurang dari :value item.',
        'file' => ':attribute harus kurang dari :value kb.',
        'numeric' => ':attribute harus kurang dari :value.',
        'string' => ':attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':attribute tidak boleh lebih dari :value item.',
        'file' => ':attribute harus kurang atau sama dengan :value kb.',
        'numeric' => ':attribute harus kurang atau sama dengan :value.',
        'string' => ':attribute harus kurang atau sama dengan :value karakter.',
    ],
    'mac_address' => ':attribute harus berupa alamat MAC yang benar.',
    'max' => [
        'array' => ':attribute tidak boleh lebih dari :max item.',
        'file' => ':attribute tidak boleh lebih dari :max kb.',
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'mimes' => ':attribute harus berupa file dengan jenis: :values.',
    'mimetypes' => ':attribute harus berupa file dengan jenis: :values.',
    'min' => [
        'array' => ':attribute tidak boleh kurang dari :min item.',
        'file' => ':attribute tidak boleh kurang dari :min kb.',
        'numeric' => ':attribute tidak boleh kurang dari :min.',
        'string' => ':attribute tidak boleh kurang dari :min karakter.',
    ],
    'multiple_of' => ':attribute harus kelipatan dari :value.',
    'not_in' => 'Pilihan :attribute tidak tersedia.',
    'not_regex' => ':attribute format tidak benar.',
    'numeric' => ':attribute harus berupa angka.',
    'password' => 'Password tidak benar.',
    'present' => ':attribute harus sudah ada.',
    'prohibited' => ':attribute tidak menerima isian.',
    'prohibited_if' => ':attribute tidak menerima isian jika :other adalah :value.',
    'prohibited_unless' => ':attribute tidak menerima isian kecuali :other terdiri dari :values.',
    'prohibits' => ':attribute tidak boleh ada di :other.',
    'regex' => ':attribute format tidak benar.',
    'required' => ':attribute harus diisi.',
    'required_array_keys' => ':attribute harus berisi isian untuk: :values.',
    'required_if' => ':attribute harus diisi jika :other adalah :value.',
    'required_unless' => ':attribute harus diisi kecuali jika :other terdiri dari :values.',
    'required_with' => ':attribute harus diisi ketika :values sudah ada.',
    'required_with_all' => ':attribute harus diisi ketika :values sudah ada.',
    'required_without' => ':attribute harus diisi ketika :values tidak ada.',
    'required_without_all' => ':attribute harus diisi ketika :values tidak ada.',
    'same' => ':attribute dan :other harus sama.',
    'size' => [
        'array' => ':attribute harus terdiri dari :size item.',
        'file' => ':attribute harus berukuran :size kb.',
        'numeric' => ':attribute harus bernilai :size.',
        'string' => ':attribute harus terdiri dari :size karakter.',
    ],
    'starts_with' => ':attribute harus berawalan: :values.',
    'string' => ':attribute harus berupa karakter.',
    'timezone' => ':attribute harus berupa waktu zona yang benar.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => ':attribute gagal diunggah.',
    'url' => ':attribute harus URL yang benar.',
    'uuid' => ':attribute harus UUID yang benar.',
    // google captcha
    'recaptcha' => 'Haiiii...!!! Apakah anda robot???',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
