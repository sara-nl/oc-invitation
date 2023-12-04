### How to create a release tar

The project will be cloned and the release tar will be build from it.

Go into the `release` folder and execute: 

`docker build --build-arg branch={main-branch} --build-arg version={version_number} --output ../build .`

This will generate from the _main-branch_ branch something like _invitation_version_number.tar_ in the `build` folder.

This file can now used in a release, or/and be un-tarred straight into the `apps` folder and the app should be ready.