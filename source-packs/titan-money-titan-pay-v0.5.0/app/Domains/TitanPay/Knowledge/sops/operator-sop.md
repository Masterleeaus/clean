# Titan Pay Operator SOP — Standard Operating Procedures

## Overview

This document provides step-by-step procedures for operators managing Titan Pay payment sessions and transactions through the admin panel.

---

## 1. Creating a Payment Session

**When to use:** When you need to initiate a payment on behalf of a customer, or when a customer payment link needs to be generated.

**Steps:**
1. Navigate to **Titan Pay** in the Filament sidebar.
2. Click **New Session**.
3. Select the **Gateway** appropriate for the customer's payment method (refer to Gateway Selection Guide if unsure).
4. Enter the **Amount** and confirm the **Currency**.
5. Set an optional **Expiry** time (default is 30 minutes).
6. Click **Create Session**.
7. The session token and QR code (where applicable) are generated automatically.
8. Share the session link or QR code with the customer.

**Expected outcome:** Session status is `opened`. Customer can now complete payment.

---

## 2. Checking a Session Status

**When to use:** When a customer reports they have paid but the system has not confirmed.

**Steps:**
1. Navigate to **Titan Pay → Sessions**.
2. Search for the session using the session token, customer name, or amount.
3. Review the **Status** field.
4. If status is `processing`, check the **Gateway Logs** tab for confirmation details.
5. If status is still `opened` or `pending` after the expected payment time:
   - For bank transfer: check **Unmatched Deposits** (see Section 4).
   - For card/PayPal/PayID: check gateway logs for webhook delivery failure.
6. If the issue cannot be resolved, escalate to the payment gateway support team.

---

## 3. Processing a Refund

**When to use:** When a customer is entitled to a refund per the Refund Policy.

**Steps:**
1. Navigate to **Titan Pay → Transactions**.
2. Locate the transaction using the session token or transaction ID.
3. Confirm the transaction status is `completed` and is within the refund window.
4. Click **Refund** on the transaction row.
5. For partial refunds, enter the refund amount. For full refunds, leave at default.
6. Click **Confirm Refund**.
7. For card gateways (Stripe, PayPal): refund is submitted to gateway immediately. Customer receives funds in 3–10 business days.
8. For bank transfer/cash: complete the manual return transfer, then click **Mark Refund Complete** in the admin panel.

**Required permission:** `titanpay.gateway.manage` or `titanpay.reconciliation.resolve`

---

## 4. Matching an Unmatched Bank Deposit

**When to use:** When a bank deposit appears in the Unmatched Deposits list.

**Steps:**
1. Navigate to **Titan Pay → Unmatched Deposits**.
2. Review the deposit details: amount, reference, depositor name, and date.
3. Locate the corresponding session in **Titan Pay → Sessions** by matching the reference or amount.
4. Click **Manually Match** on the deposit row.
5. Select the session or transaction from the search.
6. Click **Confirm Match**.
7. The session status is updated automatically.

**Note:** Only match deposits to sessions where the amount and currency agree. Do not match deposits to multiple sessions.

---

## 5. Handling a Dispute

**When to use:** When a chargeback or dispute notification is received.

**Steps:**
1. Navigate to **Titan Pay → Disputes** (or check the notification alert).
2. Review the dispute details and deadline.
3. Gather evidence: session record, transaction record, gateway log, and any proof of service.
4. Submit evidence via the relevant gateway portal:
   - **Stripe**: Stripe Dashboard → Disputes
   - **PayPal**: PayPal Resolution Centre
5. Record the dispute outcome in the admin panel.
6. If dispute is lost, note the chargeback fee in the transaction record.

**Deadline:** Respond within the gateway's dispute window (Stripe: up to 21 days, PayPal: 20 days).

---

## 6. Expiring a Session Manually

**When to use:** When a session should be cancelled before it expires naturally.

**Steps:**
1. Navigate to **Titan Pay → Sessions**.
2. Locate the session.
3. Click **Expire Session**.
4. Confirm the action. The session status is set to `expired`.
5. Notify the customer if appropriate.

---

## 7. Viewing Gateway Logs

**When to use:** For troubleshooting gateway communication issues.

**Steps:**
1. Navigate to **Titan Pay → Sessions** and open the session.
2. Click the **Gateway Logs** tab.
3. Review request/response entries for error codes or failed webhooks.
4. Do not share raw gateway log entries with customers — they may contain sensitive reference data.

---

## Security Reminders

- Never share gateway API keys, webhook secrets, or environment credentials.
- Do not export full transaction logs to CSV unless the export is access-controlled.
- Ensure only authorised users have the `titanpay.read`, `titanpay.session.create`, and `titanpay.gateway.manage` or `titanpay.reconciliation.resolve` permissions.
- Report any suspected data breach immediately to your compliance officer.
