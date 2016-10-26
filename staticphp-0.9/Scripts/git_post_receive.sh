#!/bin/sh

read oldrev newrev refname

# Find base paths
BASE_PATH=$(dirname $(readlink -f "$0"))/..
BASE_PATH="`cd $BASE_PATH;pwd`"
APP_PATH="$BASE_PATH/Application"
PUBLIC_PATH="$APP_PATH/Public"

# Git stuff
COMMIT="$oldrev..$newrev"
GIT_DIR='.git'



# Reset git
cd $BASE_PATH
git reset --hard



# Update css version
# if [ $(git diff-tree -r --name-only --no-commit-id $COMMIT | grep \\.css | wc -l) -gt 0 ]; then
#     echo "*Css file(s) modified, updating css version.. "
#
#     curl -s http://127.0.0.1/settings/increase-css-version
#
#     echo " Done"
#     echo
# fi



# Update js version
# if [ $(git diff-tree -r --name-only --no-commit-id $COMMIT | grep \\.js | wc -l) -gt 0 ]; then
#     echo "*Js file(s) modified, updating js version.. "
#
#     curl -s http://127.0.0.1/settings/increase-js-version
#
#     echo " Done"
#     echo
# fi



# Clear twig cache
# if [ $(git diff-tree -r --name-only --no-commit-id $COMMIT | grep \\.html | wc -l) -gt 0 ]; then
#     echo "*Html file(s) modified, clearing twig cache.. "
#
#     rm -r $APP_PATH/Cache/*
# fi



# Run composer update
# if [ $(git diff-tree -r --name-only --no-commit-id $COMMIT | grep composer.json | wc -l) -gt 0 ] || [ $(git diff-tree -r --name-only --no-commit-id $COMMIT | grep composer.lock | wc -l) -gt 0 ]; then
#     echo "*Composer component(-s) modified, running \"composer update\".. "
#     composer update
# fi
