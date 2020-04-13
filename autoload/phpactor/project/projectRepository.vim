function! phpactor#project#projectRepository#create() abort
  return {
        \ 'projects': {},
        \ 'isEmpty': function('s:isEmpty'),
        \ 'addProject': function('s:addProject'),
        \ 'findProjectContainingFile': function('s:findProjectContainingFile'),
        \ 'listProjects': function('s:listProjects'),
        \ 'hasProjectWithPrimaryRoot': function('s:hasProjectWithPrimaryRoot'),
        \ 'hasProject': function('s:hasProject')
        \ }
endfunction

function! s:listProjects() dict abort
  return copy(self.projects)
endfunction

function! s:findProjectContainingFile(file) dict abort
    for l:rootPath in keys(self.projects)
      let l:project = self.projects[l:rootPath]
      if l:project.containsFile(a:file)
        return l:project
      endif
    endfor

    return v:null
endfunction

function! s:addProject(project) dict abort
  " todo check type
  if v:null isnot get(self.projects, a:project.getPrimaryRootPath(), v:null)
    throw printf('Project "%s" already exists', a:project.getPrimaryRootPath())
  endif

  let self.projects[a:project.getPrimaryRootPath()] = a:project
endfunction

function! s:hasProject(project) dict abort
  return get(self.projects, a:project.getPrimaryRootPath(), v:false) isnot v:false
endfunction

function! s:hasProjectWithPrimaryRoot(path) dict abort
  return get(self.projects, a:path, v:false) isnot v:false
endfunction

function! s:isEmpty() dict abort
  return empty(self.projects)
endfunction
