<?php

return [
    'required' => ':attribute không được để trống.',
    'email' => ':attribute phải là địa chỉ email hợp lệ.',
    'unique' => ':attribute đã tồn tại trong hệ thống.',
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'min' => ['numeric' => ':attribute phải có giá trị tối thiểu :min.', 'string' => ':attribute phải có ít nhất :min ký tự.'],
    'max' => ['numeric' => ':attribute không được lớn hơn :max.', 'string' => ':attribute không được dài quá :max ký tự.'],
    'integer' => ':attribute phải là số nguyên.',
    'exists' => ':attribute đã chọn không hợp lệ.',
    'boolean' => ':attribute phải có giá trị đúng hoặc sai.',
    'attributes' => [
        'name' => 'Họ tên', 'email' => 'Email', 'password' => 'Mật khẩu', 'book_code' => 'Mã sách',
        'title' => 'Tên sách', 'author_id' => 'Tác giả', 'category_id' => 'Thể loại',
        'total_quantity' => 'Tổng số lượng', 'publication_year' => 'Năm xuất bản',
        'cover_image' => 'Ảnh bìa',
    ],
];
