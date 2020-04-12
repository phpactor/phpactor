function! phpactor#project#bufferMatcher#create(projectRepository) abort
  return {
        \ 'repository': a:projectRepository,
        \ 'getRepository': function('s:getRepository'),
        \ 'matchFileToProject': function('s:matchFileToProject')
        \ }
endfunction

function s:matchFileToProject(file) dict abort
  let l:project = self.repository.findProjectContainingFile(a:file)

  if v:null isnot l:project
    return l:project
  endif
endfunction

function s:getRepository() dict abort
  return self.repository
endfunction
