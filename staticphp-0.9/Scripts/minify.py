#!/usr/bin/env python3

"""
Css, Js file minification script. Also creates source maps for javascript files.

Requirements:

    CSS:
        pip install cssmin

    JS:
        Look here for installation instructions: https://github.com/mishoo/UglifyJS2

"""

import os
import sys
import shutil
import subprocess

BASE_PATH = os.path.dirname(os.path.dirname(os.path.realpath(__file__)))
PUBLIC_PATH = BASE_PATH+'/Application/Public'


MIN_CLEAN_BEFORE = [
    "Application/Public/assets/min/"
]


MIN_COPY_FILES = {
    "Application/Public/assets/min/": [
    ]
}

MIN_CSS = {
    "Application/Public/assets/min/css/style.min.css": [
        "Application/Public/assets/base/css/style.css"
    ]
}

MIN_JS = {
    "Application/Public/assets/min/js/main.min.js": [
        "Application/Public/assets/vendor/jquery/jquery.js",
        "Application/Public/assets/vendor/bootstrap/js/bootstrap.js"
    ]
}

# Open /dev/null
devnull = open(os.devnull, 'w')


# Define some functions
def fullPath(path):
    if path.startswith('/'):
        return path
    else:
        return BASE_PATH+'/'+path


def call(command, stdout=devnull, stdin=None, shell=False):
    return_code = 0

    try:
        return_code = subprocess.call(command, stdin=stdin, stdout=stdout, stderr=devnull, shell=shell)
    except Exception as e:
        pass

    if return_code != 0:
        print("Error executing command: \"%s\"" % " ".join(command))
        sys.exit(-1)


# CHECK FOR FILE EXISTANCE
errors = []
for to_file,paths in MIN_JS.items():
    #os.path.exists(to_path) == False:
    for filePath in paths:
        if not os.path.exists(fullPath(filePath)):
            errors.append(filePath)

for to_file,paths in MIN_CSS.items():
    for filePath in paths:
        if not os.path.exists(fullPath(filePath)):
            errors.append(filePath)

if len(errors) > 0:
    print("Missing files:")
    print(errors)
    sys.exit(-1)


# CLEAN UP
for path in MIN_CLEAN_BEFORE:
    path = fullPath(path)
    if os.path.exists(path):
        subprocess.call(['rm', '-rf', path])


# COPY FILES
for to_path,paths in MIN_COPY_FILES.items():
    to_path = fullPath(to_path)
    if os.path.exists(to_path) == False:
        os.makedirs(to_path)

    for path in paths:
        path = fullPath(path)
        call(['cp', '-rf', path, to_path])

        # Add to git
        call(['git', 'add', to_path])


# MINIFY CSS
for to_file,paths in MIN_CSS.items():
    if len(paths) == 0:
        break;

    to_path = fullPath(os.path.dirname(to_file))
    if os.path.exists(to_path) == False:
        os.makedirs(to_path)

    # Make all full paths
    paths = [fullPath(x) for x in paths]

    # Clone paths and append cat command
    cmd = paths[:]
    cmd.insert(0, 'cat')

    # Cat all files into single tmp file
    f = open(to_file+'.tmp', 'w')
    call(cmd, stdout=f)
    f.close();

    # Call cssmin and redirect output to min-file
    f = open(to_file, 'w')
    call(['cssmin'], stdin=open(to_file+'.tmp', 'r'), stdout=f)
    f.close();

    # Remove tmp file
    os.remove(to_file+'.tmp')

    # Add to git
    call(['git', 'add', to_file])


# MINIFY JS
for to_file,paths in MIN_JS.items():
    if len(paths) == 0:
        break;

    to_file = fullPath(to_file)
    map_file = to_file + '.map'
    map_url = map_file.replace(PUBLIC_PATH, '')
    to_path = os.path.dirname(to_file)

    # Make destination dir
    if os.path.exists(to_path) == False:
        os.makedirs(to_path)

    # Make all full paths
    paths = [fullPath(x) for x in paths]

    # Clone paths and append cat command
    cmd = [
        'uglifyjs',
            '--source-map', map_file,
            '--source-map-url', map_url,
            '--source-map-root', '/',
            '--prefix', str(len(PUBLIC_PATH.split('/'))-1),
            '--output', to_file,
            '--lint'
    ]
    cmd += paths
    call(cmd)

    # Add to git
    call(['git', 'add', to_file, map_file])


# Close /dev/null
devnull.close()
