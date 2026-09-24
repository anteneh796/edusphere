# EduSphere — Sprint 7 Completion

## Objective

Sprint 7 moves EduSphere from foundation stabilization into a complete, coherent school-management MVP by integrating the existing modules, closing cross-module gaps, and hardening the public website and production-facing behavior.

## Final scope

- Single school
- KG through Grade 8
- Parent is the only family-facing role
- Finance portal removed
- Hostel removed
- Library removed
- Inventory removed
- Laravel application
- Existing legacy Guardian database/model names may remain internally for compatibility; there is no Guardian portal or role.

## Phase 1 — Core academic system

### Student Management
- Student registration and profiles
- Enrollment and class placement
- Student number generation
- Parent/family linkage
- Emergency contacts
- Medical records
- Student documents and verification
- Student timeline/status history
- Internal transfers
- External transfers
- Promotion workflow
- Grade 8 graduation
- Student roster and ID card
- RBAC and policy enforcement

### Academic/Class Management
- Academic years
- Academic terms
- KG–Grade 8 grade levels
- Classes/sections
- Subjects
- Teacher/class-subject assignments
- Structural CRUD protected by academic permissions

### Attendance
- Student attendance sessions
- Teacher class scoping
- Reports
- Corrections
- Alerts
- Configuration
- Security tests

### Examinations and report cards
- Exam management
- Assigned-teacher result entry
- Published-exam constraints
- Report-card generation
- Approval and publishing
- Student/class/year integrity

## Phase 2 — School administration

### Admissions
- Public inquiries
- Inquiry-to-application conversion
- Application lifecycle
- Capacity/waitlist handling
- Approval/enrollment workflow
- Applicant documents

### HR and staff
- Staff management
- HR attendance
- Leave
- Performance
- Training
- Documents
- Recruitment
- Official letters
- Contracts/payroll as HR functionality

### Communication
- Notifications
- Parent/teacher messaging
- Parent services and requests
- Announcements
- Meeting workflows

### Documents and records
- Student documents
- Applicant documents
- HR documents
- Verification and protected downloads

## Phase 3 — Public school website

- Home
- About
- Academics
- Admissions
- Contact
- Application/inquiry
- News and article pages
- Events
- Gallery
- Faculty
- Search
- Newsletter subscription
- English/Amharic locale switching
- Robots.txt
- XML sitemap
- CMS-managed content blocks
- Public announcements

The public site uses the same KG–Grade 8 scope and does not advertise removed Finance, Hostel, Library, or Inventory modules.

## Phase 4 — Production readiness

- Route/controller integrity checks
- RBAC/route permission consistency
- Role enum/config consistency
- Navigation route validation
- Named-route uniqueness
- Removed-module route protection
- Public website rendering coverage
- Sitemap duplication prevention
- Validation and authorization tests
- CI builds frontend assets before Laravel tests
- Full PHPUnit/Pest feature suite

## Completion standard

Sprint 7 is complete only when the repository is internally consistent, the full test suite is green, and the final application scope is reflected in code, navigation, public content, and documentation.
