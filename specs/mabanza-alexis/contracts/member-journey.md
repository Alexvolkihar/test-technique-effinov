# Contract: Member Journey

## Purpose

Describe the public user-facing entry points and the access rules around account finalization.

## Entry Points

- `GET /inscription`: show the registration form
- `POST /inscription`: submit a registration request
- `GET /validation/{token}`: open the validation link from email
- `GET|POST /mot-de-passe`: create the password
- `GET /portail`: member home area
- `GET /messages`: private messaging area

## Access Rules

- Registration pages are open to anonymous visitors.
- Validation link pages are open only to the holder of the valid token.
- The password page is the only allowed page while the account is `awaiting_password`.
- Portal and messaging pages are accessible only to `active` members.
- Rejected, expired, or already consumed validation links must be refused.

## Behavioral Guarantees

- The validation link always leads to password creation.
- The portal remains inaccessible until the password is set.
- Private messages are visible only to the sender and recipient.