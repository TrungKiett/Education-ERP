# MVC Structure Documentation

## Directory Structure

```
edu/
├── config/
│   ├── database.php          # Database configuration
│   └── session.php           # Session management
├── models/                   # Model classes (Data layer)
│   ├── Database.php          # Database singleton
│   ├── Model.php             # Base model class
│   ├── User.php              # User model
│   ├── Teacher.php           # Teacher model
│   ├── Student.php           # Student model
│   ├── Classroom.php         # Classroom model
│   ├── Subject.php           # Subject model
│   ├── Schedule.php          # Schedule model
│   └── TeachingAssignment.php # Teaching assignment model
├── controllers/              # Controller classes (Logic layer)
│   ├── BaseController.php    # Base controller
│   ├── AuthController.php    # Authentication (login, register, logout)
│   ├── AdminController.php   # Admin operations
│   ├── TeacherController.php # Teacher operations
│   └── StudentController.php # Student operations
├── views/                    # View templates (Presentation layer)
│   ├── layouts/
│   │   ├── header.php        # Common header
│   │   └── footer.php        # Common footer
│   ├── auth/
│   │   ├── login.php         # Login page
│   │   └── register.php      # Registration page
│   ├── admin/
│   │   ├── dashboard.php     # Admin dashboard
│   │   ├── teachers.php      # Teacher management
│   │   ├── students.php      # Student management
│   │   ├── classrooms.php    # Classroom management
│   │   ├── subjects.php      # Subject management
│   │   ├── schedules.php     # Schedule management
│   │   └── teacher_subjects.php # Teaching assignments
│   ├── teacher/
│   │   └── dashboard.php     # Teacher schedule view
│   └── student/
│       └── dashboard.php      # Student timetable view
├── index.php                 # Router/Entry point
└── (old files can be kept for reference or removed)
```

## URL Routing

All requests go through `index.php` with action parameter:

- `index.php?action=login` - Login page
- `index.php?action=register` - Registration page
- `index.php?action=logout` - Logout
- `index.php?action=admin.dashboard` - Admin dashboard
- `index.php?action=admin.teachers` - Teacher management
- `index.php?action=admin.students` - Student management
- `index.php?action=admin.classrooms` - Classroom management
- `index.php?action=admin.subjects` - Subject management
- `index.php?action=admin.schedules` - Schedule management
- `index.php?action=teacher.dashboard` - Teacher schedule
- `index.php?action=student.dashboard` - Student timetable

## How It Works

1. **Router (index.php)**: Parses the action parameter and routes to appropriate controller
2. **Controller**: Handles business logic, uses models to interact with database
3. **Model**: Handles all database operations
4. **View**: Displays data, receives data from controller via `$data` array

## Migration Notes

Old files in `admin/`, `teacher/`, `student/` folders can be kept for reference but should eventually be migrated to the MVC structure.

