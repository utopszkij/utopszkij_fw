#!/bin/bash
# hasznalata ./tools/git.ssh gitCommand param param ....
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_utopszkij_fw 
git $1 $2 $3 $4 $5
