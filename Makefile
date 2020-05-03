# Minimal makefile for Sphinx documentation
#

# You can set these variables from the command line, and also
# from the environment for the first two.
SPHINXOPTS    ?= -W
SPHINXBUILD   ?= sphinx-build
SOURCEDIR     = doc
BUILDDIR      = doc/_build

.PHONY: help sphinx

build:
	mkdir build

build/vimdoc/.git/index: build
	git clone https://github.com/google/vimdoc build/vimdoc

build/vimdoc/build: build/vimdoc/.git
	cd build/vimdoc; python3 setup.py config
	cd build/vimdoc; python3 setup.py build 

build/bin/vimdoc: build/vimdoc/build
	cd build/vimdoc; python3 setup.py install --install-scripts ../bin --install-lib ../lib

vimdoc: build/bin/vimdoc
	./build/bin/vimdoc .

# Put it first so that "make" without argument is like "make help".
help:
	@$(SPHINXBUILD) -M help "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

sphinxwatch:
	@config/bin/watchdocs.sh

sphinx:
	@$(SPHINXBUILD) -M html "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

docs: sphinx vimdoc

clean:
	rm -Rf build
