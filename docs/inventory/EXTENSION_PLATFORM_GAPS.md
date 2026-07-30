# Extension Platform Registry Gaps

Source inventory commit: `16eef612617d32b6c63380524f2d6ba5a0d793a0`

## Filesystem extensions not mapped by Marketplace

- `Introduction`
- `TitanZeroChatbot`

## Marketplace mappings with missing provider classes

- `photo-studio` → `App\Extensions\PhotoStudio\System\PhotoStudioServiceProvider` — expected `app/Extensions/PhotoStudio/System/PhotoStudioServiceProvider.php`
- `onboarding` → `App\Extensions\Onboarding\System\OnboardingServiceProvider` — expected `app/Extensions/Onboarding/System/OnboardingServiceProvider.php`
- `affilate` → `App\Extensions\Affilate\System\AffilateServiceProvider` — expected `app/Extensions/Affilate/System/AffilateServiceProvider.php`
- `ideogram` → `App\Extensions\Ideogram\System\IdeogramServiceProvider` — expected `app/Extensions/Ideogram/System/IdeogramServiceProvider.php`
- `speechify-tts` → `App\Extensions\SpeechifyTTS\System\SpeechifyServiceProvider` — expected `app/Extensions/SpeechifyTTS/System/SpeechifyServiceProvider.php`
- `chatbot-telegram` → `App\Extensions\ChatbotTelegram\System\ChatbotTelegramServiceProvider` — expected `app/Extensions/ChatbotTelegram/System/ChatbotTelegramServiceProvider.php`
- `chatbot-whatsapp` → `App\Extensions\ChatbotWhatsapp\System\ChatbotWhatsappServiceProvider` — expected `app/Extensions/ChatbotWhatsapp/System/ChatbotWhatsappServiceProvider.php`
- `chatbot-messenger` → `App\Extensions\ChatbotMessenger\System\ChatbotMessengerServiceProvider` — expected `app/Extensions/ChatbotMessenger/System/ChatbotMessengerServiceProvider.php`
- `chatbot-instagram` → `App\Extensions\ChatbotInstagram\System\ChatbotInstagramServiceProvider` — expected `app/Extensions/ChatbotInstagram/System/ChatbotInstagramServiceProvider.php`
- `footer-menu` → `App\Extensions\FooterMenu\System\FooterMenuServiceProvider` — expected `app/Extensions/FooterMenu/System/FooterMenuServiceProvider.php`
- `demo-extension` → `App\Extensions\DemoExtension\System\DemoExtensionServiceProvider` — expected `app/Extensions/DemoExtension/System/DemoExtensionServiceProvider.php`
- `ai-chat-pro-image-chat` → `App\Extensions\AiChatProImageChat\System\AiChatProImageChatServiceProvider` — expected `app/Extensions/AiChatProImageChat/System/AiChatProImageChatServiceProvider.php`
- `ai-chat-pro-memory` → `App\Extensions\AIChatProMemory\System\AIChatProMemoryServiceProvider` — expected `app/Extensions/AIChatProMemory/System/AIChatProMemoryServiceProvider.php`
- `ai-agent-outlook` → `App\Extensions\AIAgentOutlook\System\AIAgentOutlookServiceProvider` — expected `app/Extensions/AIAgentOutlook/System/AIAgentOutlookServiceProvider.php`
- `ai-chat-pro-gmail` → `App\Extensions\AIChatProGmail\System\AIChatProGmailServiceProvider` — expected `app/Extensions/AIChatProGmail/System/AIChatProGmailServiceProvider.php`
- `ai-chat-pro-outlook` → `App\Extensions\AIChatProOutlook\System\AIChatProOutlookServiceProvider` — expected `app/Extensions/AIChatProOutlook/System/AIChatProOutlookServiceProvider.php`
- `ai-chat-pro-notion` → `App\Extensions\AIChatProNotion\System\AIChatProNotionServiceProvider` — expected `app/Extensions/AIChatProNotion/System/AIChatProNotionServiceProvider.php`
- `ai-chat-pro-google-drive` → `App\Extensions\AIChatProGoogleDrive\System\AIChatProGoogleDriveServiceProvider` — expected `app/Extensions/AIChatProGoogleDrive/System/AIChatProGoogleDriveServiceProvider.php`
- `ai-chat-pro-google-calendar` → `App\Extensions\AIChatProGoogleCalendar\System\AIChatProGoogleCalendarServiceProvider` — expected `app/Extensions/AIChatProGoogleCalendar/System/AIChatProGoogleCalendarServiceProvider.php`

## Provider classes used by multiple slugs

- None

## Dominant duplicate PHP-symbol directory pairs

- `Chatbot ↔ TitanZeroChatbot`: **768** duplicated symbols
- `AIAgentToolChatbot ↔ Chatbot ↔ TitanZeroChatbot`: **10** duplicated symbols
- `Chatbot ↔ ChatbotAgent ↔ TitanZeroChatbot`: **9** duplicated symbols
- `AiPersona ↔ Chatbot ↔ TitanZeroChatbot`: **7** duplicated symbols
- `Chatbot ↔ ModelCouncil ↔ TitanZeroChatbot`: **4** duplicated symbols
- `Chatbot ↔ OpenRouter ↔ TitanZeroChatbot`: **4** duplicated symbols
- `Chatbot ↔ ChatbotBooking ↔ TitanZeroChatbot`: **2** duplicated symbols
- `Chatbot ↔ MultiModel ↔ TitanZeroChatbot`: **2** duplicated symbols
- `Chatbot ↔ NanoBanana ↔ TitanZeroChatbot`: **2** duplicated symbols
- `AIAgent ↔ AIAgentGmail ↔ AIChatPro ↔ AIChatProDeepResearch ↔ AIChatProFolders ↔ AIChatProSkills ↔ AIImagePro ↔ AIPhotoshoot ↔ AIPlagiarism ↔ AIRealtimeImage ↔ AISocialMedia ↔ AIVideoToVideo ↔ AIWebChat ↔ AIWriterTemplates ↔ AdvancedImage ↔ AiAvatar ↔ AiCaptions ↔ AiChatProEntityHighlight ↔ AiChatProHighlightToAsk ↔ AiChatProSmartImage ↔ AiMusic ↔ AiMusicPro ↔ AiPersona ↔ AiPresentation ↔ AiVideoPro ↔ Announcement ↔ BlogPilot ↔ Canvas ↔ Chatbot ↔ ChatbotCustomerTag ↔ ChatbotEcommerce ↔ ChatbotVoice ↔ CheckoutRegistration ↔ CreativeSuite ↔ DiscountManager ↔ ElevenLabsVoiceChat ↔ FashionStudio ↔ LiveCustomizer ↔ MarketingBot ↔ MegaMenu ↔ Migration ↔ ModelCouncil ↔ MultiModel ↔ OnboardingPro ↔ OpenAIRealtimeChat ↔ PhoneCallAgent ↔ ProductPhotography ↔ SocialMedia ↔ SocialMediaAgent ↔ SocialMediaAutomation ↔ TitanZeroChatbot ↔ UGCCreator ↔ UGCFactory ↔ VideoDubbing ↔ VideoEditor ↔ Wordpress ↔ Xero`: **1** duplicated symbols
- `AIChatPro ↔ AIChatProDeepResearch ↔ Canvas`: **1** duplicated symbols

## Dominant duplicate migration-filename directory pairs

- `Chatbot ↔ TitanZeroChatbot`: **78** duplicate migration filenames
- `AiPersona ↔ Chatbot ↔ TitanZeroChatbot`: **6** duplicate migration filenames
- `AIChatProSkills ↔ Chatbot ↔ TitanZeroChatbot`: **2** duplicate migration filenames
- `Chatbot ↔ ChatbotCustomerTag ↔ TitanZeroChatbot`: **2** duplicate migration filenames
- `Chatbot ↔ ModelCouncil ↔ TitanZeroChatbot`: **2** duplicate migration filenames
- `Chatbot ↔ ChatbotEcommerce ↔ TitanZeroChatbot`: **2** duplicate migration filenames
- `Chatbot ↔ MultiModel ↔ TitanZeroChatbot`: **1** duplicate migration filenames

## Manifest and test coverage

- Extension directories without any recognised manifest: **0**
- Invalid `extension.json` documents: **93**
- Extension directories with no detected test files: **88** of **95**

### No detected tests

- `AdvancedImage`
- `AIAgentGmail`
- `AIAgentSlackChannel`
- `AIAgentToolChatbot`
- `AIAgentToolMarketingBot`
- `AIAgentToolSocialMediaAgent`
- `AIAgentWhatsappChannel`
- `AiAvatar`
- `AiCaptions`
- `AIChatPro`
- `AIChatProDeepResearch`
- `AiChatProEntityHighlight`
- `AIChatProFileChat`
- `AIChatProFolders`
- `AiChatProHighlightToAsk`
- `AIChatProSkills`
- `AiChatProSmartImage`
- `AIImagePro`
- `AiMusic`
- `AiMusicPro`
- `AiPersona`
- `AIPlagiarism`
- `AiPresentation`
- `AIRealtimeImage`
- `AISocialMedia`
- `AiVideoPro`
- `AIVideoToVideo`
- `AiViralClips`
- `AIVoiceIsolator`
- `AIWebChat`
- `AIWriterTemplates`
- `Announcement`
- `AzureOpenai`
- `AzureTTS`
- `BlogPilot`
- `Canvas`
- `ChatbotAgent`
- `ChatbotBooking`
- `ChatbotCustomerTag`
- `ChatbotEcommerce`
- `ChatbotReview`
- `ChatbotVoice`
- `ChatbotVoiceCall`
- `ChatProTempChat`
- `ChatSetting`
- `ChatShare`
- `CheckoutRegistration`
- `Cloudflare`
- `ContentManager`
- `CreativeSuite`
- `CreativeSuiteAITemplate`
- `CreativeSuiteAnnotations`
- `Cryptomus`
- `DiscountManager`
- `ElevenLabsVoiceChat`
- `FluxPro`
- `FocusMode`
- `Hubspot`
- `InfluencerAvatar`
- `Introduction`
- `LiveCustomizer`
- `Mailchimp`
- `Maintenance`
- `MarketingBot`
- `MegaMenu`
- `Menu`
- `Midjourney`
- `ModelCouncil`
- `MultiModel`
- `NanoBanana`
- `Newsletter`
- `OnboardingPro`
- `OpenAIRealtimeChat`
- `OpenRouter`
- `Perplexity`
- `ProductPhotography`
- `SeeDreamV4`
- `SEOTool`
- `SocialMedia`
- `SocialMediaAgent`
- `SocialMediaAutomation`
- `UGCCreator`
- `UGCFactory`
- `UrlToVideo`
- `VideoDubbing`
- `VideoEditor`
- `Wordpress`
- `Xero`

## Interpretation

- A missing provider mapping may mean stale registry code, removed extension source, a renamed directory or an incomplete import.
- A filesystem directory not in the marketplace map is not automatically active; it requires explicit classification.
- Duplicate symbols and migrations must be traced to copied extensions, compatibility layers or genuinely shared code before deletion.
- Zero detected tests does not prove an extension is broken, but it prevents qualification as production-ready without additional evidence.
