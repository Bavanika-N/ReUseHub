Requirements Documentation: ReUseHub

1. Introduction
This document provides a comprehensive specification of system features, user workflows, and constraints for the ReUseHub application.

2. User Roles and Personas
- Registered User / Community Member: Can list items, upload photos, browse/filter listings, send reuse requests, receive notifications, and manage their own listings/requests.
- Administrator: Can oversee all users, listings, requests, system notifications, and perform administrative audits.
- Guest / Visitor: Can browse available items and is prompted to log in to create listings or submit requests.

3. Detailed Functional Specifications

3.1 Authentication and Profile Module
- Form validation for unique email addresses.
- Encrypted password storage using BCrypt.
- Session-based authorization checking.

3.2 Item Management Module
- Listing creation with Item Name, Category, Description, Contact Number, and Thumbnail / Multi-image attachments.
- Status transitions:
  - Available (default when listed)
  - Requested (when one or more requests are active)
  - Sold Out / Given Away (when successfully transferred to a requester)

3.3 Request Management Module
- Requesters can track request status (Pending, Accepted, Rejected).
- Item owners can review incoming requests with requester contact details and approve or reject them.
- Accepting a request can automatically notify the requester and optionally mark the item as reserved or transferred.

3.4 Notifications Module
- Async / AJAX polling endpoint (api_notifications.php) to fetch unread alerts.
- Dedicated notifications management view (notifications.php) with read/unread toggle.

3.5 Administrative Control Module
- Dedicated /admin suite with sidebar navigation.
- Manage user accounts (view details, delete accounts).
- Manage item listings and moderate inappropriate content.
- Monitor request transaction history across the platform.

4. Data Requirements
- Database: reusehub
- Tables:
  - users: User credentials and roles.
  - items: Master table of item listings.
  - item_images: Secondary gallery photos linked to items.
  - requests: Transaction requests connecting items and requesters.
  - notifications: User-specific event messages.
