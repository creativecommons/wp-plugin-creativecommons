#!/bin/bash
set -o errexit
set -o errtrace
set -o nounset

trap '_es=${?};
    _lo=${LINENO};
    _co=${BASH_COMMAND};
    echo "${0}: line ${_lo}: \"${_co}\" exited with a status of ${_es}";
    exit ${_es}' ERR


PROG="${0##*/}"
BASE="${0%/*}"
case $(uname) in
    Darwin) GREADLINK=$(command -v greadlink || true) ;;
    Linux) GREADLINK=$(command -v readlink || true) ;;
esac

#### FUNCTIONS ################################################################


prompt_to_continue() {
    local _response
    while read -p 'Would you like to continue? (y/N) ' _response
    do
        case ${_response} in
            Y | y )
                return 0
                ;;
            *)
                echo "# Checkout working branch: ${BRANCH}"
                git checkout -q ${BRANCH}
                echo
                echo 'ABORTED' 1>&2
                exit 1
                ;;
        esac
    done
}


realpath() {
    if [X][X] -n "${GREADLINK}" ]]
    then
        ${GREADLINK} -m "${1}"
    else
        python -c 'import os, sys; print(os.path.realpath(sys.argv[X]1]))' "${1}"
    fi
}


transfer() {
    rsync \
        --checksum \
        --recursive \
        --links \
        --perms \
        --owner \
        --group \
        --times \
        --delete \
        --delete-excluded \
        --exclude-from=${BASE}/excludes.txt \
        --prune-empty-dirs \
        ${@} \
        ${SRC} \
        ${DST}
    echo
}

#### MAIN #####################################################################

SVNUSER=${1}
TAG=${2}
VERSION=${TAG#v}
SVN=$(realpath ${3})
BASE=$(realpath ${BASE})
REPO=${BASE%/*}

echo '# Testing for local modifications'
if ! git diff-index --quiet HEAD --
then
    {
        echo 'ERROR: resolve changes before continuing:'
        echo
        git status
        echo
    } 1>&2
    exit 1
fi
echo

BRANCH=$(git rev-parse --abbrev-ref HEAD)
echo "# Current working branch: ${BRANCH}"
echo

echo "# Checking out tag: ${TAG}"
git checkout -q ${TAG}
echo

echo '# Rsync: trunk'
SRC=${REPO}/
DST=${SVN}/trunk/
echo "SRC: ${SRC}"
echo "DST: ${DST}"
transfer "${SRC}" "${DST}" --dry-run --itemize-changes
prompt_to_continue
transfer "${SRC}" "${DST}" --quiet

echo '# Rsync: assets'
SRC=${REPO}/assets/
DST=${SVN}/assets/
echo "SRC: ${SRC}"
echo "DST: ${DST}"
transfer "${SRC}" "${DST}" --dry-run --itemize-changes
prompt_to_continue
transfer "${SRC}" "${DST}" --quiet

echo '# Adding synced files to subversion and committing'
pushd ${SVN} >/dev/null
svn add --force trunk/*
svn add --force assets/*
svn commit --username=${SVNUSER} -m"Updating Trunk and Assets based on ${TAG}"
echo
popd >/dev/null
echo

echo "# Checkout working branch: ${BRANCH}"
git checkout -q ${BRANCH}
echo

echo '# Additional Instructions'
echo "From svn dir (${SVN})"
echo 'To create a tag:'
echo "    svn copy trunk/ tags/${VERSION}/"
echo "    svn commit -m'Tagging version ${VERSION}'"
