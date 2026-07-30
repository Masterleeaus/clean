# Extension Platform Runtime Inventory

Source commit: `06b84cb7bb40d41d022372ecf0685d0a43ee1a95`

This inventory separates files present on disk, marketplace mappings, manifests and lifecycle behaviour from verified installation, qualification and activation.

## Totals

- Extension directories: **95**
- Marketplace provider mappings: **112**
- Filesystem directories not mapped: **2**
- Missing mapped provider classes: **19**
- Duplicate PHP symbols across extension directories: **810**
- Duplicate migration filenames across extension directories: **93**

## Extension summary

| Directory | Files | Manifest | Providers | Routes | Migrations | Tests | Marketplace mapped |
|---|---:|---|---:|---:|---:|---:|---:|
| `AdvancedImage` | 47 | extension.json | 1 | 0 | 2 | 0 | yes |
| `AIAgent` | 169 | extension.json | 1 | 0 | 20 | 1 | yes |
| `AIAgentGmail` | 33 | extension.json | 1 | 0 | 1 | 0 | yes |
| `AIAgentSlackChannel` | 7 | extension.json | 1 | 0 | 0 | 0 | yes |
| `AIAgentToolChatbot` | 12 | extension.json | 1 | 0 | 0 | 0 | yes |
| `AIAgentToolMarketingBot` | 11 | extension.json | 1 | 0 | 0 | 0 | yes |
| `AIAgentToolSocialMediaAgent` | 12 | extension.json | 1 | 0 | 0 | 0 | yes |
| `AIAgentWhatsappChannel` | 6 | extension.json | 1 | 0 | 0 | 0 | yes |
| `AiAvatar` | 16 | extension.json | 1 | 0 | 1 | 0 | yes |
| `AiCaptions` | 27 | extension.json | 1 | 0 | 2 | 0 | yes |
| `AIChatPro` | 47 | extension.json | 1 | 0 | 7 | 0 | yes |
| `AIChatProDeepResearch` | 18 | extension.json | 1 | 0 | 2 | 0 | yes |
| `AiChatProEntityHighlight` | 16 | extension.json | 1 | 0 | 2 | 0 | yes |
| `AIChatProFileChat` | 9 | extension.json | 1 | 0 | 0 | 0 | yes |
| `AIChatProFolders` | 12 | extension.json | 1 | 0 | 2 | 0 | yes |
| `AiChatProHighlightToAsk` | 12 | extension.json | 1 | 0 | 2 | 0 | yes |
| `AIChatProSkills` | 23 | extension.json | 1 | 0 | 2 | 0 | yes |
| `AiChatProSmartImage` | 13 | extension.json | 1 | 0 | 1 | 0 | yes |
| `AIImagePro` | 151 | extension.json | 1 | 0 | 7 | 0 | yes |
| `AiMusic` | 12 | extension.json | 1 | 0 | 1 | 0 | yes |
| `AiMusicPro` | 16 | extension.json | 1 | 0 | 3 | 0 | yes |
| `AiPersona` | 24 | extension.json | 1 | 0 | 6 | 0 | yes |
| `AIPhotoshoot` | 95 | extension.json | 1 | 0 | 5 | 1 | yes |
| `AIPlagiarism` | 9 | extension.json | 1 | 0 | 1 | 0 | yes |
| `AiPresentation` | 28 | extension.json | 1 | 0 | 3 | 0 | yes |
| `AIRealtimeImage` | 27 | extension.json | 1 | 0 | 4 | 0 | yes |
| `AISocialMedia` | 71 | extension.json | 1 | 0 | 13 | 0 | yes |
| `AiVideoPro` | 25 | extension.json | 1 | 0 | 6 | 0 | yes |
| `AIVideoToVideo` | 17 | extension.json | 1 | 0 | 2 | 0 | yes |
| `AiViralClips` | 21 | extension.json | 1 | 2 | 0 | 0 | yes |
| `AIVoiceIsolator` | 4 | extension.json | 1 | 0 | 0 | 0 | yes |
| `AIWebChat` | 18 | extension.json | 1 | 0 | 2 | 0 | yes |
| `AIWriterTemplates` | 5 | extension.json | 1 | 0 | 1 | 0 | yes |
| `Announcement` | 13 | extension.json | 1 | 0 | 1 | 0 | yes |
| `AzureOpenai` | 9 | extension.json | 1 | 0 | 0 | 0 | yes |
| `AzureTTS` | 6 | extension.json | 1 | 0 | 0 | 0 | yes |
| `BlogPilot` | 74 | extension.json | 1 | 0 | 2 | 0 | yes |
| `Canvas` | 10 | extension.json | 1 | 0 | 0 | 0 | yes |
| `Chatbot` | 1548 | extension.json | 27 | 7 | 93 | 54 | yes |
| `ChatbotAgent` | 30 | extension.json | 1 | 0 | 0 | 0 | yes |
| `ChatbotBooking` | 11 | extension.json | 1 | 0 | 0 | 0 | yes |
| `ChatbotCustomerTag` | 10 | extension.json | 1 | 0 | 2 | 0 | yes |
| `ChatbotEcommerce` | 17 | extension.json | 1 | 0 | 2 | 0 | yes |
| `ChatbotReview` | 7 | extension.json | 1 | 0 | 0 | 0 | yes |
| `ChatbotVoice` | 57 | extension.json | 1 | 0 | 5 | 0 | yes |
| `ChatbotVoiceCall` | 12 | extension.json | 1 | 0 | 0 | 0 | yes |
| `ChatProTempChat` | 12 | extension.json | 1 | 0 | 0 | 0 | yes |
| `ChatSetting` | 24 | extension.json | 1 | 0 | 0 | 0 | yes |
| `ChatShare` | 8 | extension.json | 1 | 0 | 0 | 0 | yes |
| `CheckoutRegistration` | 14 | extension.json | 1 | 0 | 1 | 0 | yes |
| `Cloudflare` | 8 | extension.json | 1 | 0 | 0 | 0 | yes |
| `ContentManager` | 10 | extension.json | 1 | 0 | 0 | 0 | yes |
| `CreativeSuite` | 111 | extension.json | 1 | 0 | 1 | 0 | yes |
| `CreativeSuiteAITemplate` | 10 | extension.json | 1 | 0 | 0 | 0 | yes |
| `CreativeSuiteAnnotations` | 17 | extension.json | 1 | 0 | 0 | 0 | yes |
| `Cryptomus` | 7 | extension.json | 1 | 0 | 0 | 0 | yes |
| `DiscountManager` | 27 | extension.json | 1 | 0 | 5 | 0 | yes |
| `ElevenLabsVoiceChat` | 24 | extension.json | 1 | 0 | 2 | 0 | yes |
| `FashionStudio` | 109 | extension.json | 1 | 0 | 9 | 1 | yes |
| `FluxPro` | 14 | extension.json | 1 | 0 | 0 | 0 | yes |
| `FocusMode` | 7 | extension.json | 1 | 0 | 0 | 0 | yes |
| `Hubspot` | 6 | extension.json | 1 | 0 | 0 | 0 | yes |
| `InfluencerAvatar` | 7 | extension.json | 1 | 0 | 0 | 0 | yes |
| `Introduction` | 9 | extension.json | 1 | 0 | 0 | 0 | no |
| `LiveCustomizer` | 11 | extension.json | 1 | 0 | 1 | 0 | yes |
| `Mailchimp` | 6 | extension.json | 1 | 0 | 0 | 0 | yes |
| `Maintenance` | 5 | extension.json | 1 | 0 | 0 | 0 | yes |
| `MarketingBot` | 177 | extension.json | 1 | 0 | 24 | 0 | yes |
| `MegaMenu` | 22 | extension.json | 1 | 0 | 3 | 0 | yes |
| `Menu` | 5 | extension.json | 1 | 0 | 0 | 0 | yes |
| `Midjourney` | 11 | extension.json | 1 | 0 | 0 | 0 | yes |
| `Migration` | 26 | extension.json | 1 | 0 | 2 | 1 | yes |
| `ModelCouncil` | 12 | extension.json | 1 | 0 | 2 | 0 | yes |
| `MultiModel` | 11 | extension.json | 1 | 0 | 1 | 0 | yes |
| `NanoBanana` | 17 | extension.json | 1 | 0 | 0 | 0 | yes |
| `Newsletter` | 7 | extension.json | 1 | 0 | 0 | 0 | yes |
| `OnboardingPro` | 32 | extension.json | 1 | 0 | 5 | 0 | yes |
| `OpenAIRealtimeChat` | 7 | extension.json | 1 | 0 | 1 | 0 | yes |
| `OpenRouter` | 15 | extension.json | 1 | 0 | 0 | 0 | yes |
| `Perplexity` | 6 | extension.json | 1 | 0 | 0 | 0 | yes |
| `PhoneCallAgent` | 83 | extension.json | 1 | 0 | 12 | 1 | yes |
| `ProductPhotography` | 14 | extension.json | 1 | 0 | 2 | 0 | yes |
| `SeeDreamV4` | 12 | extension.json | 1 | 0 | 0 | 0 | yes |
| `SEOTool` | 13 | extension.json | 1 | 0 | 0 | 0 | yes |
| `SocialMedia` | 143 | extension.json | 1 | 0 | 19 | 0 | yes |
| `SocialMediaAgent` | 117 | extension.json | 1 | 0 | 9 | 0 | yes |
| `SocialMediaAutomation` | 64 | extension.json | 1 | 0 | 7 | 0 | yes |
| `TitanZeroChatbot` | 1542 | extension.json | 27 | 7 | 93 | 53 | no |
| `UGCCreator` | 38 | extension.json | 1 | 0 | 4 | 0 | yes |
| `UGCFactory` | 54 | extension.json | 1 | 0 | 3 | 0 | yes |
| `UrlToVideo` | 37 | extension.json | 1 | 2 | 0 | 0 | yes |
| `VideoDubbing` | 25 | extension.json | 1 | 0 | 2 | 0 | yes |
| `VideoEditor` | 39 | extension.json | 1 | 0 | 6 | 0 | yes |
| `Wordpress` | 5 | extension.json | 1 | 0 | 1 | 0 | yes |
| `Xero` | 10 | extension.json | 1 | 0 | 2 | 0 | yes |

## Marketplace activation model

- Discovery feature-gated: **true**
- Registers all mapped providers when enabled: **true**
- Install mutation uses GET: **true**
- Uninstall mutation uses GET: **true**

## Lifecycle evidence

### Install

- `downloads_remote_zip`: `True`
- `zip_extract_to_direct`: `True`
- `zip_entry_validation_detected`: `False`
- `signature_verification_detected`: `False`
- `chmod_0777`: `True`
- `forced_migration`: `True`
- `forced_asset_publish`: `True`
- `clears_caches`: `True`
- `transaction_detected`: `False`
- `rollback_path_detected`: `False`
- `chmod_folder`: `['chmod($folderPath, 0777)']`

### Uninstall

- `deletes_directory`: `True`
- `clears_cache`: `True`
- `database_rollback_detected`: `False`
- `exceptions_swallowed`: `True`
- `provider_uninstall_hook`: `True`

## Findings

- **confirmed activation risk:** When extension discovery is enabled, MarketplaceServiceProvider registers every mapped provider rather than only installed and qualified extensions.
- **confirmed unsafe lifecycle route:** Extension install/uninstall mutations are exposed as authenticated GET routes instead of CSRF-protected state-changing requests.
- **confirmed archive validation gap:** Remote extension ZIP content is extracted directly without detected per-entry traversal, symlink or destination validation.
- **confirmed supply-chain verification gap:** No extension archive signature or public-key verification was detected before extraction and execution.
- **confirmed permission hardening gap:** New extension directories are chmod 0777.
- **confirmed upgrade rollback risk:** Installation runs forced migrations after extraction without a detected transactional install or rollback/restore path.
- **confirmed uninstall residue risk:** Uninstall deletes extension files and invokes hooks but no database migration rollback was detected.
- **registry drift:** 2 extension directories are not represented in the static marketplace provider map.
- **registry drift:** 19 mapped provider classes are missing from expected paths.
- **duplicate class risk:** 810 PHP symbols occur in more than one extension directory.

## Required platform rule

- Files on disk do not make an extension installed, entitled, qualified or active.
- Discovery must select only installed, enabled, compatible and qualified extensions.
- Each active extension requires a validated manifest, unique slug/key/provider, dependency check, tenant/package gate and health result.
- Install/uninstall must use authorised state-changing requests, verified archives, staging, rollback and auditable lifecycle records.
- Providers, routes, migrations, permissions, menus and capability keys must register exactly once.
- Extensions may add capabilities but may not replace host identity, tenancy, WorkCore authority, messaging authority or Vault.

Full per-extension manifests, paths, symbols, duplicate groups and registry reconciliation are stored in the JSON inventory.
