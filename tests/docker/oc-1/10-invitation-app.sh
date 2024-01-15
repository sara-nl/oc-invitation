#!/bin/bash

# This script triggers installation of the Invitation app right before server startup

echo 'Install Invitation app'
occ app:enable invitation
