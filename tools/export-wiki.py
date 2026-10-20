#!/usr/bin/env python3
"""Export public Wiki pages with page URLs while preserving repository file links."""
from pathlib import Path
import argparse,re

def normalize(text, pages):
    """Rewrite only non-image links whose target is an exported Wiki page."""
    def replace(match):
        label,target=match.groups()
        parts=re.match(r'([^#?]+)(.*)$',target)
        if not parts:return match.group(0)
        path,fragment=parts.groups()
        if path.endswith('.md'):
            name=path.rsplit('/',1)[-1][:-3]
            if name in pages and ('://' not in path or '/wiki/' in path):
                return '['+label+']('+path[:-3]+fragment+')'
        return match.group(0)
    return re.sub(r'(?<!!)\[([^\]]+)\]\(([^)]+)\)',replace,text)

def main():
    """Write only the explicit generated public page set to an existing checkout."""
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('destination',type=Path)
    args=parser.parse_args()
    root=Path(__file__).resolve().parents[1]/'docs/wiki'
    files=sorted(root.glob('*.md'));pages={p.stem for p in files}
    assert not any('development' in name.lower() for name in pages)
    args.destination.mkdir(parents=True,exist_ok=True)
    for p in files:
        text=normalize(p.read_text(),pages)
        for target in re.findall(r'(?<!!)\[[^\]]+\]\(([^)]+)\)',text):
            assert not re.search(r'(?:^|/)(?:Development|development)(?:[.#/]|$)',target)
            assert not re.search(r'(?:^|/)(?:'+ '|'.join(re.escape(n) for n in pages)+r')\.md(?:[#?]|$)',target),target
        (args.destination/p.name).write_text(text.rstrip()+'\n')
    print('Exported',len(files),'formatted Wiki pages; repository sources retained.')

if __name__=='__main__':main()
