# KYAF Food Donation Management System — Implementation Plan & SRS Traceability

Source of truth: **KYAF SRS v1.1** (uploaded). Where the SRS and the original "master prompt" differ, the SRS wins
(confirmed with the owner: location = SRS levels only; four role-specific login pages; Bangla UI everywhere).

## Stages (all built into one integrated Laravel project)
1. Skeleton, config, 21-table schema (+`password_histories`), models, seeders.
2. Authentication (role-specific login pages, lockout, throttling, idle timeout), password policy/history/reset, role middleware, audit log.
3. Public site, donor registration, donor dashboard, Bangla donation form with service-area popup.
4. Donation lifecycle engine (`DonationService`) and the staff / manager / admin screens that drive it.
5. Staff recruitment (application, review, invitation, 5-seat team capacity), service areas, location master data + CSV import.
6. Notifications, reports (+CSV), contact messages, settings, audit-log viewer.
7. Tests, documentation, Windows scripts, packaging.

## Architecture
| Concern | Where |
|---|---|
| Routes | `routes/web.php` (role groups: `role:ADMIN`, `role:AREA_MANAGER`, `role:STAFF`, `role:DONOR`) |
| Role authorisation | `App\Http\Middleware\EnsureRole` (403 + audit) and ownership checks in controllers/services |
| Business rules | `App\Services\DonationService`, `TeamService`, `ApplicationService`, `PasswordPolicy` |
| Audit / notifications | `Audit::log()`, `Notifier` |
| Validation rules | `App\Rules\BdPhone`, `StrongPassword`, `Nid`; `lang/bn/validation.php` |
| UI | Blade layouts `public` / `dashboard` (per-role theme + sidebar), anonymous components `x-input/x-password/x-select/x-textarea/x-status`, `public/js/kyaf.js` |

## Business-rule traceability
| SRS rule | Implementation |
|---|---|
| BR-01/03 five staff per manager, never a 6th | `TeamService::addStaff` (row lock + count), `reservedSeats` counts valid invitations; "Understaffed" banner on dashboards |
| BR-02 one manager ↔ one area; thana in ≤ 1 area | unique `service_areas.manager_id`; `ServiceAreaController::validated` + JS picker |
| BR-04 Bangla donation pages | whole UI is Bangla |
| BR-05/06 donation fields, meals 1–100 000, expiry ≥ 2 h ahead | `Donor\DonationController::store`, `DonationService::submit` |
| BR-08 tracking code `DON-YYYY-NNNNNN`, donor id `DNR-NNNNNN` | `DonationService::submit`, `Donor::nextId` (locked) |
| BR-09 donor sees only own donations | `Donor\DonationController::own`, `MediaController` |
| BR-10 role isolation | `EnsureRole`, tests in `AuthenticationTest` |
| BR-12 lifecycle / BR-15 history | `DonationService::transition` + `DonationStatusHistory` (append-only) |
| BR-13 route by thana, refuse if no active area/manager | `DonationService::submit` |
| BR-14 only assigned staff acts; only own team can be assigned | `requireAssignedStaff`, `assign` |
| BR-16 rejection reason mandatory | `verify`, `decide`, `cancel` |
| BR-17 expiry re-checked, auto-expire | `expiryGuard`, `expireOverdue` (`kyaf:expire-donations`, scheduled) |
| BR-18/19 quantity limits | verify ≤ declared, collect ≤ verified, distribute ≤ collected |
| BR-20/21/22 applications, one open per NID, 72 h single-use invite | `StaffApplicationController`, `ApplicationService`, `InvitationController` |
| BR-23/24 one role per user, role fixed | `user_accounts.role_id`; read-only Roles page |
| BR-25/26/27/33 password policy, lock-out, history, 30-min idle | `StrongPassword`, `LoginController`, `PasswordPolicy`, `IdleTimeout` |
| BR-28 notifications to donor + staff + manager | `Notifier::donation` |
| BR-29 public Find-Food shows no personal data | `PublicController::findFood` selects only public columns |
| BR-30 reports scoped per role, masked exports | `ReportService`, `ReportController::export` |
| BR-32 admin override needs a reason, audit-logged | `DonationService::requireManagerOrAdmin`, `ApplicationService::authorize`, admin screens |
| BR-34 geographic master data by Admin only | `Admin\LocationController` (+CSV import) |
| BR-36 duplicate donation warning (10 min) | `DonationService::submit` + confirmation checkbox |

## Decisions where the SRS is silent (all documented in README_BANGLA.md §6)
Collection scheduled time = approval + 1 h (capped before expiry); staff cannot be suspended/removed while holding open donations;
failed collection keeps the donation *Approved* (staff may retry); a donation is *Distributed* only after every batch is completed/cancelled and ≥ 1 batch delivered meals;
e-mail/SMS not sent (Issue C-08) — links are shown on screen and logged; profile photo and contact-change verification codes not built.

## Verification status
See README_BANGLA.md §8. Static checks and JS syntax check were run; PHP/Composer/MySQL/PHPUnit could **not** be run in the build environment.
