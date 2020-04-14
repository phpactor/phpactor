augroup phpactor
  autocmd!
  autocmd FileType php call phpactor#project#bufferMatcher#assignFileToProject(expand('<afile>'))
augroup END
