#!/usr/bin/env bash

set -euo pipefail

loadHelp() {
    cat <<'EOF'
TYPO3 extension test runner based on the TYPO3BestPractices/tea container setup.

Usage: Build/Scripts/runTests.sh [options] [additional tool arguments]

Options:
    -s <suite>   composer, composerUpdateMin, composerUpdateMax, lintPhp,
                 phpCsFixer, phpstan, rector, fractor, unit, functional, check
    -p <version> PHP version: 8.2, 8.3, 8.4 or 8.5 (default: 8.2)
    -t <version> TYPO3 version: 13.4 or 14.3 (default: 13.4)
    -b <runtime> Container runtime: docker or podman (default: auto-detect)
    -n           Dry-run for PHP-CS-Fixer, Rector and Fractor
    -h           Show this help
EOF
}

TEST_SUITE="unit"
PHP_VERSION="8.2"
CORE_VERSION="13.4"
CONTAINER_BIN=""
DRY_RUN=""

while getopts "b:s:p:t:nh" option; do
    case "${option}" in
        b)
            CONTAINER_BIN="${OPTARG}"
            ;;
        s)
            TEST_SUITE="${OPTARG}"
            ;;
        p)
            PHP_VERSION="${OPTARG}"
            ;;
        t)
            CORE_VERSION="${OPTARG}"
            ;;
        n)
            DRY_RUN="1"
            ;;
        h)
            loadHelp
            exit 0
            ;;
        *)
            loadHelp >&2
            exit 2
            ;;
    esac
done
shift $((OPTIND - 1))

if ! [[ "${PHP_VERSION}" =~ ^8\.(2|3|4|5)$ ]]; then
    echo "Unsupported PHP version: ${PHP_VERSION}" >&2
    exit 2
fi

if ! [[ "${CORE_VERSION}" =~ ^(13\.4|14\.3)$ ]]; then
    echo "Unsupported TYPO3 version: ${CORE_VERSION}" >&2
    exit 2
fi

if [[ -z "${CONTAINER_BIN}" ]]; then
    if command -v podman >/dev/null 2>&1; then
        CONTAINER_BIN="podman"
    elif command -v docker >/dev/null 2>&1; then
        CONTAINER_BIN="docker"
    else
        echo "This script requires Docker or Podman." >&2
        exit 1
    fi
fi

if ! command -v "${CONTAINER_BIN}" >/dev/null 2>&1; then
    echo "Container runtime not found: ${CONTAINER_BIN}" >&2
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/../.." >/dev/null && pwd)"
IMAGE_PHP="ghcr.io/typo3/core-testing-php${PHP_VERSION/./}:latest"

mkdir -p "${ROOT_DIR}/.cache" "${ROOT_DIR}/.Build/public/typo3temp/var/tests"

CONTAINER_ARGS=(
    --rm
    --init
    -e "COMPOSER_CACHE_DIR=.cache/composer"
    -e "COMPOSER_ROOT_VERSION=6.0.x-dev"
    -e "XDEBUG_MODE=off"
    -v "${ROOT_DIR}:${ROOT_DIR}"
    -w "${ROOT_DIR}"
)

if [[ "$(uname)" != "Darwin" && "${CONTAINER_BIN}" == "docker" ]]; then
    CONTAINER_ARGS+=(--user "$(id -u):$(id -g)")
fi

runPhpContainer() {
    "${CONTAINER_BIN}" run "${CONTAINER_ARGS[@]}" "${IMAGE_PHP}" "$@"
}

runFunctionalTestContainer() {
    "${CONTAINER_BIN}" run "${CONTAINER_ARGS[@]}" \
        -e "typo3DatabaseDriver=pdo_sqlite" \
        "${IMAGE_PHP}" "$@"
}

composerVersionConstraints() {
    printf '%s' "--with=typo3/cms-backend:^${CORE_VERSION} --with=typo3/cms-core:^${CORE_VERSION} --with=typo3/cms-extbase:^${CORE_VERSION} --with=typo3/cms-fluid:^${CORE_VERSION} --with=typo3/cms-frontend:^${CORE_VERSION} --with=typo3/cms-install:^${CORE_VERSION}"
}

composerUpdateMax() {
    runPhpContainer /bin/sh -c "composer config --unset platform.php >/dev/null 2>&1 || true; composer update -W --no-interaction --no-progress $(composerVersionConstraints)"
}

composerUpdateMin() {
    runPhpContainer /bin/sh -c "composer config platform.php ${PHP_VERSION}.0; composer update -W --prefer-lowest --no-interaction --no-progress $(composerVersionConstraints); status=\$?; composer config --unset platform.php; exit \$status"
}

phpCsFixer() {
    local options=(fix --config=Build/php-cs-fixer/config.php -v)
    if [[ -n "${DRY_RUN}" ]]; then
        options+=(--dry-run --diff)
    fi
    runPhpContainer .Build/bin/php-cs-fixer "${options[@]}"
}

rector() {
    local options=(process --config=Build/rector/config.php)
    if [[ -n "${DRY_RUN}" ]]; then
        options+=(--dry-run)
    fi
    runPhpContainer .Build/bin/rector "${options[@]}"
}

fractor() {
    local options=(process --config=Build/fractor/config.php)
    if [[ -n "${DRY_RUN}" ]]; then
        options+=(--dry-run)
    fi
    runPhpContainer .Build/bin/fractor "${options[@]}"
}

phpstan() {
    runPhpContainer php -dxdebug.mode=off .Build/bin/phpstan analyse \
        -c "Build/phpstan/TYPO3_${CORE_VERSION}/phpstan.neon" \
        --no-progress --no-interaction --memory-limit 4G "$@"
}

runCheckSuite() {
    runPhpContainer composer check:composer:normalize
    runPhpContainer composer check:composer:psr-verify
    runPhpContainer composer check:json:lint
    runPhpContainer composer check:php:lint
    runPhpContainer composer check:typoscript:lint
    runPhpContainer composer check:xliff:lint
    runPhpContainer composer check:yaml:lint
    DRY_RUN="1" phpCsFixer
    DRY_RUN="1" rector
    DRY_RUN="1" fractor
    phpstan
    runPhpContainer .Build/bin/phpunit -c Build/phpunit/UnitTests.xml
    runFunctionalTestContainer .Build/bin/phpunit -c Build/phpunit/FunctionalTests.xml
}

case "${TEST_SUITE}" in
    composer)
        runPhpContainer composer "$@"
        ;;
    composerUpdateMax)
        composerUpdateMax
        ;;
    composerUpdateMin)
        composerUpdateMin
        ;;
    lintPhp)
        runPhpContainer composer check:php:lint
        ;;
    phpCsFixer)
        phpCsFixer
        ;;
    phpstan)
        phpstan "$@"
        ;;
    rector)
        rector
        ;;
    fractor)
        fractor
        ;;
    unit)
        runPhpContainer .Build/bin/phpunit -c Build/phpunit/UnitTests.xml "$@"
        ;;
    functional)
        runFunctionalTestContainer .Build/bin/phpunit -c Build/phpunit/FunctionalTests.xml "$@"
        ;;
    check)
        runCheckSuite
        ;;
    *)
        echo "Unknown test suite: ${TEST_SUITE}" >&2
        loadHelp >&2
        exit 2
        ;;
esac
