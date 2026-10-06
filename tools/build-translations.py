#!/usr/bin/env python3
"""Build the host POT, Du/Sie PO/MO and WordPress editor JSON from one source."""
from pathlib import Path
import json,re,struct
root=Path(__file__).resolve().parents[1]
domain='connect-for-shopware'
source=json.loads((root/'languages/messages.json').read_text())
version=re.search(r'Version:\s*(\S+)',(root/'connect-for-shopware.php').read_text())[1]
strings=set()
for p in list((root/'src').glob('*.php'))+list((root/'bricks').glob('*.php'))+list((root/'assets').glob('*.js'))+[root/'connect-for-shopware.php',root/'includes/updater-translations.php']:
 for m in re.finditer(r"(?:__|esc_html__|esc_attr__)\(\s*'((?:[^'\\]|\\.)*)'",p.read_text()):strings.add(m[1])
for p in (root/'blocks').glob('*/block.json'):strings.add('block title\x04'+json.loads(p.read_text())['title'])
for locale,catalog in source.items():
 missing=strings-catalog.keys()
 if missing:raise ValueError('Missing '+locale+' messages: '+repr(sorted(missing)))
 metadata=f'Project-Id-Version: Connect for Shopware {version}\nLanguage: {locale}\nContent-Type: text/plain; charset=UTF-8\nPlural-Forms: nplurals=2; plural=(n != 1);\n'
 messages={'':metadata,**{s:catalog[s] for s in sorted(strings)}};keys=sorted(messages);count=len(keys);offset=28+16*count;orig=b'';ot=[];trans=b'';tt=[]
 for key in keys:b=key.encode();ot.append((len(b),offset+len(orig)));orig+=b+b'\0'
 transoffset=offset+len(orig)
 for key in keys:b=messages[key].encode();tt.append((len(b),transoffset+len(trans)));trans+=b+b'\0'
 mo=struct.pack('<7I',0x950412de,0,count,28,28+8*count,0,0)+b''.join(struct.pack('<2I',*x) for x in ot+tt)+orig+trans
 (root/'languages'/f'{domain}-{locale}.mo').write_bytes(mo)
 entries=[]
 for key in keys:
  context=''
  if '\x04' in key:context,msgid=key.split('\x04',1);context='msgctxt '+json.dumps(context,ensure_ascii=False)+'\n'
  else:msgid=key
  entries.append(context+'msgid '+json.dumps(msgid,ensure_ascii=False)+'\nmsgstr '+json.dumps(messages[key],ensure_ascii=False))
 (root/'languages'/f'{domain}-{locale}.po').write_text('\n\n'.join(entries)+'\n')
 jed={'translation-revision-date':'2026-10-06 00:00+0000','generator':'DECKERWEB','domain':domain,'locale_data':{'messages':{'':{'domain':domain,'lang':locale,'plural-forms':'nplurals=2; plural=(n != 1);'},**{s:[catalog[s]] for s in sorted(strings)}}}}
 (root/'languages'/f'{domain}-{locale}-dw-sw-editor.json').write_text(json.dumps(jed,ensure_ascii=False)+'\n')
entries=['msgid ""\nmsgstr "Content-Type: text/plain; charset=UTF-8\\n"']
for key in sorted(strings):
 context=''
 if '\x04' in key:ctx,msgid=key.split('\x04',1);context='msgctxt '+json.dumps(ctx)+'\n'
 else:msgid=key
 entries.append(context+'msgid '+json.dumps(msgid,ensure_ascii=False)+'\nmsgstr ""')
(root/'languages'/f'{domain}.pot').write_text('\n\n'.join(entries)+'\n')
print(f'Built {len(strings)} complete messages in de_DE and de_DE_formal plus the English POT.')
