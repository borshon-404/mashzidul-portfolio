#!/usr/bin/env python3
"""QC audit for the built site: tag balance, single H1, alt text, local link/asset resolution."""
import os, re, sys, glob
from html.parser import HTMLParser

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'site')

class P(HTMLParser):
    VOID = {'meta','link','img','br','hr','input','source','path','circle','rect','line','ellipse','use','stop'}
    def __init__(self):
        super().__init__(); self.attrs=[]; self.stack=[]; self.errs=[]
    def handle_starttag(self,t,a):
        self.attrs.append((t,dict(a)))
        if t not in self.VOID: self.stack.append(t)
    def handle_startendtag(self,t,a): self.attrs.append((t,dict(a)))
    def handle_endtag(self,t):
        if t in self.VOID: return
        if self.stack and self.stack[-1]==t: self.stack.pop()
        elif t in self.stack:
            while self.stack and self.stack[-1]!=t: self.errs.append('unclosed '+self.stack.pop())
            self.stack.pop()
        else: self.errs.append('stray </%s>'%t)

problems=[]
files=sorted(glob.glob(os.path.join(ROOT,'**','*.html'),recursive=True))
for f in files:
    rel=os.path.relpath(f,ROOT)
    src=open(f,encoding='utf-8').read()
    p=P(); p.feed(src)
    if p.stack: problems.append(f'{rel}: unclosed at EOF: {",".join(p.stack[:5])}')
    if p.errs: problems.append(f'{rel}: {"; ".join(p.errs[:4])}')
    for t,a in p.attrs:
        if t=='img' and 'alt' not in a: problems.append(f'{rel}: img without alt')
        for k in ('href','src'):
            v=a.get(k)
            if not v or v.startswith(('http','#','mailto:','tel:')): continue
            target=v.split('#')[0].split('?')[0]
            if not target: continue
            fp=os.path.join(ROOT,target.lstrip('/'))
            if not (os.path.isfile(fp) or os.path.isfile(os.path.join(fp,'index.html'))):
                problems.append(f'{rel}: MISSING {v}')
    h1=len(re.findall(r'<h1[\s>]',src))
    if h1!=1: problems.append(f'{rel}: h1 count={h1}')
    if '<title></title>' in src: problems.append(f'{rel}: empty title')

print(f'Audited {len(files)} HTML pages.')
if problems:
    print('PROBLEMS:')
    for m in problems[:80]: print('  -',m)
    sys.exit(1)
print('AUDIT CLEAN: links, assets, alt text, heading structure all OK.')
