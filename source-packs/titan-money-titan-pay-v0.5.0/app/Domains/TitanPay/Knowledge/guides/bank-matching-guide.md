# Bank Deposit Matching Guide

## Overview

When a payer uses the **Bank Transfer** gateway, Titan Pay cannot automatically receive a real-time confirmation. Instead, the operator's bank sends a deposit notification (via bank feed, CSV import, or API), which is stored as a `Titan PayBankDeposit` record. The deposit matching process links the deposit to the correct payment session and transaction.

## Matching Process

1. **Deposit ingested** — a `Titan PayBankDeposit` is created with fields including `amount`, `currency`, `reference`, `depositor_name`, `depositor_bsb`, and `depositor_account`.
2. **Match attempt** — Titan Pay compares the deposit reference against outstanding session tokens and transaction references.
3. **Score assignment** — a `match_score` (0–100) is calculated based on reference match, amount match, and currency match. A `match_method` field records how the match was determined.
4. **Threshold** — deposits with `match_score` ≥ 80 are auto-matched; those below threshold are flagged for operator review.
5. **Transaction link** — on successful match, the `transaction_id` on the deposit is set and the linked session is advanced to `processing` or `completed`.

## Match Score Factors

| Factor | Weight | Notes |
|--------|--------|-------|
| Reference exact match | 50 | Session token or invoice reference found in deposit `reference` field |
| Amount match | 30 | Deposit amount equals session amount ± tolerance |
| Depositor name match | 10 | Depositor name matches user name on file |
| Currency match | 10 | Deposit currency matches session currency |

## Manual Matching

Operators can manually link a deposit to a session via the Titan Pay admin panel. Manual matches record `match_method = "manual"` and `match_score = 100`.

## Unmatched Deposits

Deposits that cannot be matched within 7 days are flagged as unresolved. The operator receives a notification and must either match or reject the deposit. Rejected deposits are recorded for audit purposes but do not affect session status.

## Deposit Fields Reference

| Field | Type | Description |
|-------|------|-------------|
| `bank_account_id` | integer | The receiving bank account |
| `transaction_id` | integer | Linked Titan Pay transaction (set on match) |
| `amount` | decimal | Deposited amount |
| `currency` | string | Currency code |
| `depositor_name` | string | Name as provided by payer's bank |
| `depositor_bsb` | string | Payer's BSB |
| `depositor_account` | string | Payer's account number (masked) |
| `reference` | string | Payment reference provided by payer |
| `deposited_at` | datetime | Date and time of deposit |
| `status` | string | `unmatched`, `matched`, `rejected` |
| `match_score` | integer | 0–100 confidence score |
| `match_method` | string | `auto`, `manual`, or `none` |

## Security Note

`depositor_account` and `depositor_bsb` are sensitive financial identifiers. These must never be included in agent responses or logs. The agent must only reference deposits by their internal ID or partially masked reference.
