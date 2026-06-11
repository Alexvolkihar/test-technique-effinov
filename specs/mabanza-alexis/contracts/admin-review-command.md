# Contract: Admin Review Command

## Purpose

Allow an administrator to approve or reject a registration request without a web admin interface.

## Command

```bash
bin/console app:review-registration <registrationId> --decision=approve|reject [--reason="..."]
```

## Inputs

- `registrationId`: numeric identifier of the pending request
- `--decision`: required value, either `approve` or `reject`
- `--reason`: optional text used when rejecting a request

## Successful Approval

- The request status changes from `pending` to `approved`.
- A member account is created with an internal identifier.
- A single-use validation token is generated.
- A validation email is sent to the candidate.

## Successful Rejection

- The request status changes from `pending` to `rejected`.
- No member account is created.
- No validation link is sent.

## Failure Cases

- The command must refuse an unknown identifier.
- The command must refuse requests that are already approved or rejected.
- The command must refuse an empty or invalid decision value.