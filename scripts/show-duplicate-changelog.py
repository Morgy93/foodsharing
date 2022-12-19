#!/usr/bin/python

try:
    fp = open('./CHANGELOG.md')
    line = fp.readline()
    cnt = 1

    lines = []
    while line:
        line = line.strip()

        starts = ['- ', '* ']
        for s in starts:
            if line.startswith(s):
                line = line[len(s):]

        symbols = ['@', '!', '#']
        for s in symbols:
            idx = line.find(s)
            if idx>=0:
                line = line[:idx]

        if len(line)>0:
            lines.append(line)

        line = fp.readline()
    print('lines: ', len(lines), '\n')

    duplicates = [x for n, x in enumerate(lines) if x in lines[:n]]
    print('duplicates:')
    for x in duplicates:
        print(x)
finally:
    fp.close()
