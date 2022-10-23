#!/bin/bash

# exit shell as soon as a command fails (exit status not 0):
set -o errexit
# Treat unset variables as an error when substituting:
set -o nounset

if [[ "$CI_ENVIRONMENT_NAME" == "production" ]]
then
  ssh deploy@dragonfruit.foodsharing.network sudo systemctl restart fs-chatserver.service
fi
