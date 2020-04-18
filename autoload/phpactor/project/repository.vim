function! phpactor#project#repository#create() abort
  return {
        \ 'projects': {},
        \ 'isEmpty': function('s:isEmpty'),
        \ 'addProject': function('s:addProject'),
        \ 'findProjectContainingFile': function('s:findProjectContainingFile'),
        \ 'hasProjectWithPrimaryRoot': function('s:hasProjectWithPrimaryRoot'),
        \ 'hasProject': function('s:hasProject')
        \ }
endfunction

function! s:findProjectContainingFile(file) dict abort
    for l:rootPath in keys(l:self.projects)
      let l:project = l:self.projects[l:rootPath]
      if l:project.containsFile(a:file)
        return l:project
      endif
    endfor

    return v:null
endfunction

function! s:addProject(project) dict abort
  " todo check type
  if v:null isnot get(l:self.projects, a:project.primaryRootPath, v:null)
    throw printf('Project "%s" already exists', a:project.primaryRootPath)
  endif

  let l:self.projects[a:project.primaryRootPath] = a:project
endfunction

function! s:hasProject(project) dict abort
  return get(l:self.projects, a:project.primaryRootPath, v:false) isnot v:false
endfunction

function! s:hasProjectWithPrimaryRoot(path) dict abort
  return get(l:self.projects, a:path, v:false) isnot v:false
endfunction

function! s:isEmpty() dict abort
  return empty(l:self.projects)
endfunction
