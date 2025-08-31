
## About This Task

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

## PROJECT RUNNING

## RUN THIS TO start the quee
1: php artisan queue:work --timeout=600

## RUN THIS TO start Backend
2: php artisan serve --host=192.168.1.111 --port=8000

## RUN THIS TO start Frontend
3: npm run dev


## MY IMPLEMENTATION PROCESS
Implementation Strategy for Your Dashboard
Based on your current design and the 100K+ diamond dataset, here's how to implement each requirement:
1. Big Data Upload Management
Dashboard Integration - Replace that disabled "Upload BigData" navigation item with a functional link. The upload form should follow your existing design patterns with the same rounded-3xl cards, green color scheme, and consistent spacing.
Upload Form Architecture - Create a multi-step wizard that fits your dashboard style: Step 1 (File Selection), Step 2 (Column Mapping & Validation Preview), Step 3 (Processing Status), Step 4 (Results Summary). Use your existing card layout with progress indicators.
File Processing Strategy - Implement chunked processing where the system reads your CSV in 2,000-row chunks, processes each chunk in separate queue jobs, and updates progress in real-time. This prevents timeout issues and memory exhaustion.
Queue System Setup - Configure Redis-backed queues with multiple workers. Each chunk job will validate data, handle duplicates, insert records, and update progress. The master job orchestrates everything and handles final cleanup.

2. Excel Export Functionality
Export Integration - Add export buttons to your existing table interface (those action buttons you already have). Implement both "Export Current View" and "Export All Data" options.
Performance-Optimized Export - Use Laravel Excel with chunked reading and writing. For large datasets, implement background export jobs that generate files and email download links rather than direct downloads.
Format Preservation - Maintain your diamond data structure with proper column formatting, data types, and even conditional formatting for things like price ranges or quality grades.

3. Advanced Filtering & Search
Search Interface - Extend your dashboard table with advanced filter panels that slide out from the side. Include filters for cut quality, carat weight ranges, price ranges, lab certification, and color grades specific to your diamond data.
Database Optimization - Create composite indexes on frequently filtered columns like cut, color, clarity, carat_weight, and total_sales_price. Use Laravel Scout with Elasticsearch or database full-text search for keyword searching.
Real-Time Filtering - Implement AJAX-based filtering that updates the table without page reloads, maintaining your smooth user experience with loading states and transitions.

4. Data Display & Navigation
Enhanced Table Component - Upgrade your existing dashboard table to handle large datasets with virtual scrolling, server-side pagination, and lazy loading. Keep your current design but add infinite scroll capability.
Detail View Modal - Create slide-over panels that show complete record details when users click the view button. Display all 26 columns in a clean, organized format with your current card styling.
Responsive Data Grid - Ensure the table works on mobile devices with horizontal scrolling, collapsible columns, and touch-friendly controls.
Technical Implementation Plan

1: Database & Queue Setup

Extend your existing database with uploads, upload_chunks, upload_errors, and diamond_data tables
Configure Redis queues and set up queue workers for background processing
Implement progress tracking using cache or database updates

2: Upload Interface

Create upload controller that integrates with your existing auth system
Build the multi-step upload wizard using your current Tailwind classes
Implement file validation, preview, and confirmation screens

3: Processing Engine

Build chunk processing jobs that handle 2,000 rows at a time
Implement error handling, duplicate detection, and data validation
Create progress tracking that updates in real-time via WebSockets or polling

4: Display & Management

Enhance your dashboard table with advanced filtering and search
Implement server-side pagination for handling 100K+ records efficiently
Add export functionality with background job processing

5: Performance Optimization

Implement database indexing strategy for your diamond data columns
Add caching for frequently accessed data and search results
Configure queue monitoring and automatic retry mechanisms

Performance Considerations for 100K+ Records
Memory Management - Process your CSV in small chunks to avoid memory limits. Each chunk job should handle only 2,000 rows and release memory immediately after processing.
Database Strategy - Use batch inserts with Laravel's upsert() method for efficient database operations. Implement proper indexing on searchable columns like cut, color, clarity, and price.
Timeout Prevention - Set reasonable timeouts for upload processing. For a 100K record file, expect 10-15 minutes of processing time with proper chunking.
User Experience - Show real-time progress updates so users know the system is working. Implement email notifications when large uploads complete.
The key is maintaining your existing design consistency while building robust background processing. Your current dashboard already shows upload history and status - you just need to make it functional for real large-scale data processing.