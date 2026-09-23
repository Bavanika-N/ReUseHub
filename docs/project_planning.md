Project Planning Document: ReUseHub

1. Project Lifecycle and Methodology
The development of ReUseHub followed an iterative Agile-based approach comprising requirements analysis, database and architecture design, frontend/backend implementation, unit and integration testing, and documentation.

2. Work Breakdown Structure (WBS) and Milestones

Phase 1: Requirement Analysis and Scope Definition
Deliverables: Problem Statement, Objectives, Scope Document
Status: Completed

Phase 2: Architecture and Database Design
Deliverables: ER Diagram, SQL Schema (schema.sql), Wireframes
Status: Completed

Phase 3: Core System Development
Deliverables: Authentication, Item CRUD, Multi-image upload
Status: Completed

Phase 4: Interaction and Transactions
Deliverables: Request workflow, Status updates, In-app notifications
Status: Completed

Phase 5: Administration and Moderation
Deliverables: Admin dashboard, User and Listing management
Status: Completed

Phase 6: Testing and Polish
Deliverables: UI responsiveness, security validation, documentation
Status: Completed

3. Risk Assessment and Mitigation

Risk: Unauthorized access to admin routes
Impact: High
Likelihood: Low
Mitigation Strategy: Enforced server-side session role checks in all admin scripts.

Risk: Inappropriate item uploads
Impact: Medium
Likelihood: Medium
Mitigation Strategy: Admin moderation suite to review and delete listings.

Risk: SQL Injection attacks
Impact: High
Likelihood: Low
Mitigation Strategy: Use prepared statements with parameterized queries across all database interactions.

Risk: File upload vulnerabilities
Impact: High
Likelihood: Low
Mitigation Strategy: Validate image MIME types, file size, and store with sanitized unique filenames.
