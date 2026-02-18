Project Architecture Rules:

1. Database Status Handling
   - All status fields must be stored as tinyInteger in database.
   - No string status columns allowed.

2. Enum Requirement
   - Every status field must have a corresponding PHP 8.1 backed Enum inside:
     app/Enums

   Example:
     BookingStatus: int

3. Model Casting
   - Laravel native enum casting must be used in model.

     protected $casts = [
         'status' => BookingStatus::class,
     ];

4. No Magic Numbers
   - Controllers, Services, and Repositories must NEVER use raw integers like 0,1,2.
   - Always use Enum cases.

     Example:
       BookingStatus::CONFIRMED

5. Service Layer Pattern (Mandatory)
   - Controller → Service → Repository (optional)
   - Controllers must NOT contain business logic.
   - All business rules must be inside Service classes.

6. Validation Rules
   - All request validation must be handled via FormRequest classes.
   - Validation failure must automatically return JSON response with HTTP 422 status.
   - Standard validation error response format:

     {
       "success": false,
       "message": "Validation failed",
       "errors": {
         "field_name": ["Error message"]
       }
     }

   - Do not manually validate inside controllers.

7. API Response Structure (Standardized)
   - All successful responses must follow:

     {
       "success": true,
       "message": "Operation successful",
       "data": { ... }
     }

   - All error responses must follow:

     {
       "success": false,
       "message": "Error message"
     }

8. File Upload & Image Storage
   - All images must be stored using Laravel Storage system.
   - Storage disk must use MinIO (S3 compatible).
   - Images must be stored under:

       storage/app/public/

   - Do NOT store files directly in public folder.
   - Use:

       Storage::disk('minio')->putFile(...)

   - Store only file path in database, never full URL.

9. Environment Configuration
   - MinIO must be configured using .env:

       FILESYSTEM_DISK=minio

   - No hardcoded bucket names or credentials allowed.

10. Clean Code Rules
   - Strict typing enabled.
   - No unused imports.
   - No inline logic in routes.
   - No duplicated logic across services.
