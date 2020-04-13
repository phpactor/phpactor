augroup phpactor
  autocmd!
  autocmd FileType php call PhpactorAdd(expand('<afile>'))
augroup END

function PhpactorAdd(filename)
  if !exists('g:phpactorLoaded')
    return
  endif

  if exists('b:project')
    return
  endif
  let l:project = g:phpactorBufferMatcher.matchFileToProject(a:filename)
  if v:null is l:project
    return
  endif
  let b:project = l:project
  if g:phpactorBufferMatcher.getRepository().hasProject(b:project)
    return
  endif

  call g:phpactorBufferMatcher.getRepository().addProject(b:project)
endfunction
