
## About This Task

## API I have Created
POST /api/register          - Register new user
{
    "name": "Gaspar Giddson",
    "email": "Gaspar@example.com",
    "password": "password123"
}


POST /api/login             - User login
{
    "email": "gaspar@gmail.com",
    "password": "password123"
}

GET  /api/user              - Get current user
GET  /api/profile           - View profile


PUT  /api/profile           - Update profile
Authorization: Bearer {your_token}
Content-Type: application/json
{
    "name": "Gaspar Smith",
    "email": "gaspar@gmail.com",
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
