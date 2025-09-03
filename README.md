
## About This Task
# Laravel Big Data Upload & Export System
## Features

- **Large File Upload**: Handle CSV files with 100k+ records
- **Chunked Processing**: Memory-efficient data processing using queue jobs
- **Smart Export System**: Automatic detection between direct streaming and queue-based exports
- **Real-time Progress Tracking**: Monitor upload and export progress
- **Advanced Filtering**: Multi-criteria search and filtering capabilities
- **Cursor-based Pagination**: Efficient handling of large datasets
- **Auto-download**: Automatic file download for completed exports
- **Email Notifications**: Email alerts for completed background jobs
- **Modern UI**: Responsive interface built with Tailwind CSS

## System Requirements

- PHP 8.1 or higher
- MySQL 5.7+ or MySQL 8.0+
- Composer 2.0+
- Node.js 16+ and npm (for frontend assets)
- Redis (recommended for queue management)
- Minimum 2GB RAM (4GB+ recommended for large datasets)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/iamgaspardev/ubx_system_practical_interview.git
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Environment Setup

Copy the environment file and configure your settings:

```bash
cp .env.example .env
```

Configure your `.env` file with the following key settings:

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

Run the migrations to create the required tables:

```bash
php artisan migrate
```

### 7. Create Storage Links

```bash
php artisan storage:link
```

### 8. Build Frontend Assets

```bash
npm run build
# For development:
# npm run dev
```

### 9. Configure Queue Tables

If using database queues:

```bash
php artisan queue:table
php artisan migrate
```

## Configuration

### Memory Optimization

Add these settings to your `php.ini` file:

```ini
memory_limit = 2048M
max_execution_time = 3600
upload_max_filesize = 512M
post_max_size = 512M
max_input_time = 3600
```

### MySQL Configuration

Add these settings to your MySQL configuration:

```sql
SET GLOBAL max_allowed_packet = 1073741824; -- 1GB
SET GLOBAL innodb_buffer_pool_size = 2147483648; -- 2GB
```

### Queue Configuration

For production, configure Redis for better queue performance:

```bash
# Install Redis
sudo apt-get install redis-server

# Start Redis service
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

## Running the Application

### 1. Start the Laravel Development Server

```bash
php artisan serve
```

### 2. Start the Queue Worker

**Important**: Always run the queue worker for background processing:

```bash
# Basic queue worker
php artisan queue:work

# With specific queue and verbose output
php artisan queue:work --queue=exports,default --verbose

# For production (daemon mode)
php artisan queue:work --daemon
```

### 3. Monitor Queue Jobs (Optional)

```bash
php artisan queue:monitor
```

## Usage Guide

### Data Upload

1. Navigate to `/bigdata/create`
2. Select your CSV file (supports files with 100k+ records)
3. Upload will be processed in chunks automatically
4. Monitor progress on the upload status page

### Data Export

The system automatically chooses the best export method:

- **Small datasets (< 25k)**: Excel format with immediate download
- **Medium datasets (25k - 95k)**: CSV format with streaming download
- **Large datasets (95k+)**: Queue-based processing with email notification + browser alert

#### Export Options:

1. **Standard Export**: Recommended for most use cases
2. **Quick Export**: For large datasets, forces CSV format

### Filtering & Search

Use the comprehensive filtering system:
- Text search across multiple fields
- Cut, Color, Clarity dropdowns
- Carat weight range
- Price range
- Lab certification filter

## Architecture Overview

### Key Components

1. **BigDataController**: Main controller handling uploads and exports
2. **ProcessUploadJob**: Queue job for processing large CSV uploads
3. **ExportLargeDataJob**: Queue job for handling large dataset exports
4. **DiamondData Model**: Main data model with advanced filtering
5. **Upload Model**: Tracks upload progress and status


### Database Structure

```sql
-- Main data table
diamond_data (
    id, cut, color, clarity, carat_weight, 
    total_sales_price, lab, upload_id, 
    created_at, updated_at, ...
)

-- Upload tracking
uploads (
    id, user_id, filename, status, 
    progress_percentage, total_records,
    created_at, updated_at
)

-- Queue jobs
jobs (
    id, queue, payload, attempts, 
    available_at, created_at
)
```

## Performance Optimization

### For Large Datasets

1. **Cursor-based Pagination**: Uses `WHERE id > last_id` instead of OFFSET
2. **Memory Management**: Automatic garbage collection and memory limits
3. **Chunked Processing**: Processes data in configurable chunks
4. **Streaming Responses**: Direct output streaming for downloads

### Recommended Server Configuration

- **Memory**: 4GB+ RAM
- **Storage**: SSD recommended for better I/O performance
- **PHP**: PHP 8.1+ with OPcache enabled
- **Database**: MySQL 8.0+ with proper indexing

## Troubleshooting

### Common Issues

#### 1. Memory Limit Exceeded
```bash
# Increase PHP memory limit
ini_set('memory_limit', '2048M');
```

#### 2. Queue Jobs Not Processing
```bash
# Ensure queue worker is running
php artisan queue:work --verbose

# Check failed jobs
php artisan queue:failed
```

#### 3. Large File Upload Issues
```bash
# Check PHP settings
php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time)"
```

#### 4. MySQL Connection Issues
```sql
-- Check MySQL settings
SHOW VARIABLES LIKE 'max_allowed_packet';
SHOW VARIABLES LIKE 'wait_timeout';
```

### Debug Commands

```bash
# Clear all caches
php artisan optimize:clear

# View logs
tail -f storage/logs/laravel.log

# Check queue status
php artisan queue:monitor

# Restart queue workers
php artisan queue:restart
```

## API Endpoints

### Main Routes

- `GET /bigdata` - Main dashboard with data listing
- `GET /bigdata/create` - Upload form
- `POST /bigdata` - Process file upload
- `GET /bigdata/export` - Export data (auto-detects format)
- `GET /bigdata/export/status` - Check export status
- `GET /bigdata/export/download/{filename}` - Download export file

### API Response Examples

#### Export Status Response
```json
{
    "status": "ready",
    "filename": "data_export_1_2024_01_15_14_30_45.csv",
    "file_size": "22.13 MB",
    "total_records": "99,999",
    "download_url": "/export/download/data_export_1_2024_01_15_14_30_45.csv",
    "is_recent": true
}
```

## Security Considerations

1. **File Validation**: Only CSV files are accepted
2. **User Authentication**: All routes require authentication
3. **File Access Control**: Users can only access their own exports
4. **Input Sanitization**: All user inputs are validated and sanitized
5. **Rate Limiting**: Prevents abuse of export functionality

## API I have Created
POST /api/register          - Register new user
{
    "name": "Gaspar Giddson",
    "email": "gaspar@ubxinterview.com",
    "password": "password123"
}


POST /api/login             - User login
{
    "email": "gaspar@ubxinterview.com",
    "password": "password123"
}

GET  /api/user              - Get current user
GET  /api/profile           - View profile


PUT  /api/profile           - Update profile
Authorization: Bearer {your_token}
Content-Type: application/json
{
    "name": "Gaspar Smith",
    "email": "gaspar@ubxinterview.com",
    "current_password": "oldpassword",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
    "device_id":""
}

## POST /api/profile/image     - Upload profile image
Authorization: Bearer {your_token}
Content-Type: multipart/form-data

image: [FILE] (jpeg, png, jpg, gif - max 2MB)


DELETE /api/profile/image   - Delete profile image
POST /api/logout            - Logout user

## Base URL
http://localhost:8000/api
Authorization: Bearer {your_token}

### Run Test

```bash
# Clear all caches
php artisan test --filter BigDataExportTest

```
