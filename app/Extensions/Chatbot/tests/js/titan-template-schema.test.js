const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, '../../resources/titan-apps/TemplateSchemas');
const files = fs.readdirSync(dir).filter(file => /^titan-.*\.json$/.test(file));

if (files.length !== 15) throw new Error(`Expected 15 schemas, got ${files.length}`);

for (const file of files) {
  const schema = JSON.parse(fs.readFileSync(path.join(dir, file)));
  for (const key of ['identity','navigation','home','chat','workcore','offline','permissions','privacy','notifications','settings_sections','preview_states']) {
    if (!(key in schema)) throw new Error(`${file} missing ${key}`);
  }
  if (schema.navigation.primary.length !== 4) throw new Error(`${file} must have 4 primary links`);
  const banned = schema.navigation.primary.map(item => item.label.toLowerCase());
  for (const label of ['assistant','chat','settings','help']) {
    if (banned.includes(label)) throw new Error(`${file} has banned primary label ${label}`);
  }
  if (schema.chat.context_policy.send_full_record !== false) throw new Error(`${file} must prohibit full-record chat context`);
  if (schema.offline.conflict_rules.server_authoritative !== true) throw new Error(`${file} must keep server authority`);
}

const train = JSON.parse(fs.readFileSync(path.join(dir, 'titan-train.json')));
if (train.online_only !== true || train.offline.enabled !== false) throw new Error('Titan Train must remain online-only');
if (train.navigation.primary.some(item => item.offline !== false)) throw new Error('Titan Train primary views cannot claim offline support');

console.log(`Validated ${files.length} Titan app schemas including native Titan Train`);
