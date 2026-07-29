# KYC Requirements

## Overview

KYC (Know Your Customer) requirements apply to operators using Titan Pay for regulated payment activities. This document outlines the KYC data collected, stored, and verified within the Titan Pay module.

## Operator KYC

Operators (companies) using Titan Pay for payment acceptance may be required to complete KYC verification depending on their transaction volumes and applicable jurisdiction. This is managed at the platform level and is a prerequisite for enabling certain gateways (e.g. high-volume Stripe, PayPal).

## Payer Identity

For standard e-commerce transactions, Titan Pay does not collect payer KYC beyond standard user registration data. The `user_id` field on a session links to the platform user record, which may contain:
- Full name
- Email address
- Phone number (if provided)
- KYC submission records (stored separately in the platform `kyc_submissions` table)

## High-Risk Transactions

Operators in regulated industries (financial services, high-value goods) may be required to collect additional payer identity verification before a session can be completed. In these cases:
- A KYC check is triggered at session creation if the amount exceeds the configured threshold.
- The session status remains `pending` until KYC verification is confirmed.
- KYC verification is managed via the platform's `/api/user/kyc/submit` endpoint.

## Data Handling

KYC data handling requirements:
- **Storage**: KYC documents (e.g. ID scans) must not be stored within Titan Pay module database tables. They belong in a separate, access-controlled secure storage.
- **Access**: KYC records must only be accessible to authorised operators and compliance officers. Standard Titan Pay session operators do not have access to raw KYC documents.
- **Retention**: KYC records must be retained for a minimum of 5 years from the date of the transaction or the end of the customer relationship (whichever is later), per AML/CTF regulations.
- **Deletion**: KYC data deletion requests must be evaluated against applicable legal retention requirements before actioning.

## AML/CTF Compliance

Operators processing high-value transactions may have obligations under Anti-Money Laundering and Counter-Terrorism Financing (AML/CTF) legislation. Titan Pay supports:
- Recording of customer identity data linked to transactions via the `meta` field (reference only, not raw documents).
- Flagging of transactions exceeding configurable thresholds for manual review.
- Audit trail of all session and transaction records for regulatory reporting.

## Data Privacy

KYC data containing personally identifiable information (PII) is subject to applicable privacy legislation (e.g. Australian Privacy Act 1988, GDPR where applicable). PII must never be:
- Returned in AI agent responses
- Included in exported reports without appropriate access controls
- Logged in application or gateway logs

## Agent Restrictions

The Titan Pay AI agent must not:
- Return national ID numbers, passport numbers, or tax file numbers in responses.
- Confirm whether a specific individual has passed or failed KYC (refer operator to the compliance portal).
- Access or summarise raw KYC documents.
