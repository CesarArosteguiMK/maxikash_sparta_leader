Instalar estos hooks en el clon (Git 2.9+):

  git config core.hooksPath .githooks

Así el pre-commit evita subir *.log y quita BOM en PHP antes de cada commit.
En Windows usar Git Bash o la misma consola donde corres git.
