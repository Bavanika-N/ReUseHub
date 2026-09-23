Requirements Baseline: ReUseHub

1. Purpose and Scope
This document outlines the agreed-upon baseline requirements for the ReUseHub application. Any changes beyond this baseline require formal review and approval.

2. Core Functional Requirements Baseline

Req ID: REQ-F01
Module: User Authentication
Description: User registration, login with secure password hashing (password_hash), and session management.
Priority: High (Must Have)

Req ID: REQ-F02
Module: Role Management
Description: Role-based access control distinguishing regular community members from system administrators.
Priority: High (Must Have)

Req ID: REQ-F03
Module: Item Management
Description: Add, edit, view details, and manage status (Available, Requested, Sold Out) for items.
Priority: High (Must Have)

Req ID: REQ-F04
Module: Image Uploads
Description: Support multiple image uploads per item listing.
Priority: Medium (Should Have)

Req ID: REQ-F05
Module: Request Workflow
Description: Item requesters can submit request; item owners can accept or reject requests.
Priority: High (Must Have)

Req ID: REQ-F06
Module: Notification System
Description: In-app notification alerts for item requests, status decisions, and new listing alerts.
Priority: Medium (Should Have)

Req ID: REQ-F07
Module: Search and Filter
Description: Filter items by category, keyword search, and availability status.
Priority: High (Must Have)

Req ID: REQ-F08
Module: Admin Dashboard
Description: Overview analytics, user management, item moderation, and transaction request monitoring.
Priority: High (Must Have)

3. Non-Functional Requirements Baseline

- Usability: Clean, intuitive UI with responsive layout compatible across desktop and mobile devices.
- Performance: Page response time under 1.5 seconds under standard local server load.
- Security: SQL injection prevention via prepared statements, XSS escaping, and protected administrative routes.
- Reliability: Relational integrity enforced through foreign keys with cascading delete rules.
