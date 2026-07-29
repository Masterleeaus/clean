# Gateway Selection Guide

## Overview

Titan Pay supports multiple payment gateways. Each gateway is implemented as an adapter that fulfils the `GatewayContract` interface. The correct gateway is selected when creating a session and determines how the payer completes the payment.

## Available Gateways

| Gateway Slug | Adapter Class | Description |
|---|---|---|
| `stripe` | `StripeGatewayAdapter` | Card payments via Stripe. Supports real-time confirmation webhooks. |
| `paypal` | `PayPalGatewayAdapter` | PayPal checkout flow. Redirects to PayPal and returns via webhook. |
| `bank_transfer` | `BankTransferGatewayAdapter` | Manual BSB/account bank transfer. Requires deposit matching. Fee: $0.00. |
| `payid` | `PayIdGatewayAdapter` | Australian PayID (Osko/NPP). Near-instant settlement. |
| `crypto` | `CryptomusGatewayAdapter` | Cryptocurrency payments via Cryptomus. |
| `cash` | `CashGatewayAdapter` | In-person cash payment recorded by operator. |
| `default` | `DefaultGatewayAdapter` | Fallback; returns a pending status without gateway action. |

## Selection Guidelines

Use the following decision criteria when advising on gateway selection:

### For instant digital payments
- Prefer **Stripe** for card payments where the payer has a card.
- Prefer **PayID** for Australian domestic payments requiring near-instant settlement.
- Prefer **PayPal** when the payer specifically requests PayPal checkout.

### For cryptocurrency
- Use **Crypto (Cryptomus)** only when the operator has configured a Cryptomus API key and the payer prefers crypto.

### For bank transfers
- Use **Bank Transfer** when the payer will initiate a manual deposit. This requires deposit matching to confirm payment (see Bank Matching Guide).
- Bank transfers have zero processing fees but require manual reconciliation.

### For in-person or cash payments
- Use **Cash** for face-to-face or till transactions recorded by an operator.

## Gateway Availability

Gateway availability is configured per company. Operators can enable or disable gateways in the Titan Pay module settings. The agent must not suggest a gateway that is not enabled for the operator's company.

## Fees

Each gateway may apply a fee. The fee is stored on the `Titan PayTransaction` record in the `fee` field. `net_amount` = `amount` − `fee`. Bank Transfer and Cash gateways have zero fees by default.

## Webhook Handling

Stripe, PayPal, and Cryptomus gateways use webhooks to confirm payment. Webhook events are received by the Titan Pay webhook controller and stored as `Titan PayWebhookEvent` records before being processed. Session status is updated to `processing` on webhook receipt and `completed` once settlement is confirmed.
