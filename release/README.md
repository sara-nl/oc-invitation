### How to create a release tar

The project will be cloned and the release tar will be build from it.

Go into the `release` folder and execute: 

`docker build --build-arg branch={some_branch} --build-arg version={version_number} --output ../build .`

This will generate from the _some_branch_ branch something like _invitation_version_number.tar_ in the `build` folder.

This file can now be used in a release, or/and be extracted after which you can copy the `invitation` folder straight into the `apps` folder. The app should then be ready to be activated.