# Titan Train LMS — Pass 3 Verification

## Source checks

- Titan Train schema is the fifteenth native Chatbot template.
- Titan Suite registry config is present under `TitanSuiteTemplates/titan-train`.
- Template schema discovery merges individual schema files with the existing index.
- Online-only navigation excludes the offline queue and offline-sync settings.
- Native workspace owns only the `titan-train` operational element.
- Generic operational screens remain responsible for the other fourteen apps.
- Client sends same-origin credentials, CSRF token and active company context.
- Lesson completion and assessment start use authenticated Titan Train APIs.
- Training channel buttons dispatch authority-preserving Titan Channels events.
- No IndexedDB, SQLite, outbox, sync cursor or offline-operation reference exists in the Titan Train workspace.

## Tests

- `titan-train-native-workspace.test.js`
- updated `titan-template-schema.test.js`
- `ChatbotPwaNativeContractTest.php`
- PHP syntax checks for modified PHP files
- JavaScript syntax checks for the native client and workspace

## Runtime limitation

The repository excludes `vendor/autoload.php`, so Laravel boot, migrations and PHPUnit execution remain deployment checks. Static PHP and JavaScript verification is performed in this pass.
