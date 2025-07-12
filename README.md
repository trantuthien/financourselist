# Finan Course List Plugin for Moodle

A modern, responsive course listing plugin that provides an enhanced way to display and browse courses with customizable branding, advanced filtering, and multi-language support.

## Description

The Finan Course List plugin transforms the standard course browsing experience with a beautiful, grid-based layout that showcases courses with their images, statistics, and detailed information. Perfect for institutions wanting to provide a modern, engaging course catalog.

## Key Features

- **🎨 Modern Grid Layout**: Responsive 2-5 column grid display
- **🔍 Advanced Search & Filtering**: Search by course name/description and filter by categories  
- **🌍 Multi-language Support**: Built-in English and Vietnamese translations
- **🎨 Customizable Branding**: Full color customization through admin settings
- **📊 Statistics Dashboard**: Course counts, enrollment numbers, and ratings overview
- **📱 Mobile Responsive**: Optimized for all device sizes
- **🖼️ Flexible Image Display**: Course images, category icons, or hybrid modes
- **⚡ Smart Pagination**: Efficient navigation with page info

## Screenshots

See the `/screenshots/` directory for visual examples of the plugin in action.

## Installation

1. Download the plugin ZIP file
2. Upload via Site Administration > Plugins > Install plugins
3. Follow the installation wizard
4. Configure settings in Local plugins > Finan Course List

## Configuration

Access plugin settings via:
**Site Administration → Local plugins → Finan Course List**

### Color Settings
- Primary Color (buttons, links, highlights)
- Secondary Color (gradients, accents)  
- Dark Green (text, borders)
- Light Green (backgrounds)
- Header Text Color (title/subtitle color)

### Display Settings
- Show/hide statistics section
- Course image display mode
- Courses per page (default: 15)
- Grid columns (2-5 columns)

### Content Settings
- Custom page title and subtitle
- Multi-language support

## Technical Requirements

- **Moodle**: 4.1 - 4.4+
- **PHP**: 7.4+
- **License**: GPL v3

## Support & Development

- **Author**: Finan Team
- **Bug Reports**: https://github.com/trantuthien/financourselist/issues
- **Source Code**: https://github.com/trantuthien/financourselist
- **Documentation**: https://github.com/trantuthien/financourselist/wiki

## License

This plugin is licensed under the GNU General Public License v3.0. See LICENSE file for details.

## Tính năng

- ✅ Hiển thị danh sách khóa học trong grid 3 cột responsive
- ✅ Tìm kiếm theo tên và mô tả khóa học
- ✅ Lọc theo danh mục khóa học
- ✅ Phân trang thông minh với thông tin chi tiết
- ✅ Thống kê tổng quan (số khóa học, học viên, đánh giá)
- ✅ Thiết kế tuân thủ brand guidelines Finan
- ✅ Tích hợp navigation Moodle
- ✅ Responsive design (Desktop/Tablet/Mobile)

## Cài đặt

### Cách 1: Upload qua Admin Interface

1. Đăng nhập vào Moodle với quyền admin
2. Vào **Site Administration > Plugins > Install plugins**
3. Upload file `courselist-plugin.tar.gz`
4. Làm theo hướng dẫn cài đặt

### Cách 2: Copy thủ công

1. Giải nén file plugin
2. Copy thư mục `courselist/` vào `/path/to/moodle/local/`
3. Vào **Site Administration > Notifications**
4. Chạy upgrade database

## Yêu cầu hệ thống

- Moodle 4.1+ 
- PHP 7.4+
- Web server có hỗ trợ mod_rewrite (tùy chọn)

## Sử dụng

Sau khi cài đặt, truy cập:
- URL: `/local/courselist/index.php`
- Navigation: Menu "Danh sách khóa học" sẽ xuất hiện tự động

## Tùy chỉnh

### Thay đổi màu sắc brand

Chỉnh sửa CSS variables trong `index.php`:

```css
:root {
    --finan-primary: #0FD46B;      /* Màu chính */
    --finan-secondary: #0FB56B;    /* Màu phụ */
    --finan-dark-green: #4D825E;   /* Màu xanh đậm */
    --finan-light-green: #EEFFF2;  /* Màu xanh nhạt */
}
```

### Thay đổi số khóa học/trang

Chỉnh sửa trong `index.php`:
```php
$perpage = 15; // Thay đổi số này
```

### Thay đổi grid layout

Chỉnh sửa CSS:
```css
.courses-grid {
    grid-template-columns: repeat(4, 1fr); /* 4 cột thay vì 3 */
}
```

## Cấu trúc thư mục

```
courselist/
├── index.php          # Trang chính
├── version.php         # Thông tin plugin
├── lib.php            # Navigation hooks
└── README.md          # Documentation
```

## Tương thích

- ✅ Theme Academi
- ✅ Theme Boost
- ✅ Theme Classic
- ✅ Theme Moove
- ✅ Các theme custom khác

## Hỗ trợ

Nếu gặp vấn đề, kiểm tra:

1. **Permission lỗi**: Đảm bảo thư mục có quyền đọc
2. **Database lỗi**: Chạy upgrade database
3. **Style lỗi**: Xóa cache theme
4. **Navigation lỗi**: Purge all caches

## Changelog

### v1.0.0 (2024-07-12)
- Phiên bản đầu tiên
- Grid 3 cột responsive  
- Tìm kiếm và lọc
- Phân trang thông minh
- Brand guidelines Finan

## License

GPL v3 - tương thích với Moodle core