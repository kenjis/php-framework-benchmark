#!/bin/sh

PLATFORM=`uname`

# Find base path
BASE_PATH=$(dirname $(readlink -f "$0"))/..
BASE_PATH="`cd $BASE_PATH;pwd`"

# Git stuff
COMMIT="HEAD"
LOCAL_BRANCH="`git name-rev --name-only HEAD`"
TRACKING_REMOTE="`git config branch.$LOCAL_BRANCH.remote`"
TRACKING_BRANCH="$TRACKING_REMOTE/$LOCAL_BRANCH"



# Test non-ascii filenames
echo "*Testing non-ascii filenames.. "
if [ $(git diff --cached --name-only --diff-filter=A -z $COMMIT | LC_ALL=C tr -d '[ -~]\0' | wc -c) -gt 0 ]; then
    echo "Error: Attempt to add a non-ascii file name."
    echo
    echo "This can cause problems if you want to work"
    echo "with people on other platforms."
    echo
    echo "To be portable it is advisable to rename the file ..."
    echo
    exit 1
fi
echo " Done"
echo



# Test for whitespace errors
echo "*Testing for whitespace errors.. "
git diff-index --cached --check $COMMIT --
if [ "$?" != "0" ]; then
    echo "!!! ERROR !!!"
    exit 1
fi
echo " Done"
echo



# Test for most common debug symbols
#echo "*Testing for debug symbols.. "
#if [ "$(git diff --cached $COMMIT | grep -P 'print_r|console\\.log')" != "" ]; then
#    echo "!!! ERROR !!!"
#    echo "$(git diff --cached $COMMIT | grep -P 'print_r|console\\.log')"
#    exit 1
#fi
#echo " Done"
#echo



# Trying to compile all php files
if [ $(git diff-index --cached --name-only --diff-filter=ACMR $COMMIT | grep \\.php | wc -l) -gt 0 ]; then
    echo "*PHP file(-s) changed, running lint.."

    for file in $(git diff-index --cached --name-only --diff-filter=ACMR $COMMIT | grep \\.php); do
        php -l $file > /dev/null

        if [ "$?" != "0" ]; then
            echo "!!! ERROR: $COMMIT $file"
            exit 1
        fi
    done

    echo " Done"
    echo
fi



# Compile css, js
if [ $(git diff-index --cached --name-only $COMMIT | grep \\.css | wc -l) -gt 0 ] || [ $(git diff-index --cached --name-only $COMMIT | grep \\.js | wc -l) -gt 0 ]; then
    echo "*CSS or JS file(s) modified, compressing.. "
    $BASE_PATH/Scripts/minify.py

    if [ "$?" != "0" ]; then
        echo
        echo "Something went wrong while trying to minify css or javascript.."
        echo
        exit 1
    fi

    echo " Done"
    echo
fi



# Dump sql schema
#echo "*Dumping database schema .. "
#if [ "$PLATFORM" = "Linux" ]; then
#   sudo -u postgres pg_dump --schema-only --no-owner pm > "$BASE_PATH/Application/Files/db_schema.sql"
#elif [ "$PLATFORM" = "FreeBSD" ]; then
#   sudo -u pgsql pg_dump --schema-only --no-owner --no-privileges pm > "$BASE_PATH/Application/Files/db_schema.sql"
#fi

#git add "$BASE_PATH/Application/Files/db_schema.sql"
#echo " Done"
#echo



# Check git remote changes
if [ "$TRACKING_REMOTE" != "" ]; then
    echo "*Checking git remote changes.. "

    git fetch > /dev/null
    git merge --no-commit --no-ff --quiet $TRACKING_BRANCH > /dev/null 2>&1

    if [ "$?" != "0" ]; then
        echo
        echo "Remote repository has some new updates that conflicts with your changes. Stash your files first, then do 'git merge $TRACKING_BRANCH' and then apply stash by 'git stash pop'"
        echo
        exit 1
    fi

    echo " Done"
    echo

    echo "*Merging latest remote changes.. "
    git merge $TRACKING_BRANCH --no-edit --quiet
    echo " Done"
    echo
fi
