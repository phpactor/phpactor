function! phpactor#project#assigner#assignProjectToBuffer(filename, allowReassign, allowInteractive)
  if !exists('g:loaded_phpactor')
    return
  endif

  if exists('b:phpactorProject') && (v:false is a:allowReassign)
    return
  endif
  let l:project = g:phpactorProjectAssigner.resolveProjectForFile(a:filename, a:allowInteractive)
  if v:null is l:project
    return
  endif
  let b:phpactorProject = l:project
  echomsg printf('Project with root "%s" has been created.', l:project.primaryRootPath)
  if g:phpactorProjectAssigner.repository.hasProject(b:phpactorProject)
    return
  endif

  call g:phpactorProjectAssigner.repository.addProject(b:phpactorProject)
endfunction

function! phpactor#project#assigner#create(
      \ projectRepository,
      \ projectRootMarkers,
      \ forbiddenProjectRoots,
      \ initialCwd,
      \ noninteractiveResolverNames
      \) abort
  let l:forbiddenProjectRoots = a:forbiddenProjectRoots
  if index(l:forbiddenProjectRoots, '/') < 0
    call add(l:forbiddenProjectRoots, '/')
  endif

  let l:resolverNames = filter(copy(a:noninteractiveResolverNames), function('s:isValidResolverName'))
  if (index(l:resolverNames, 'initialCwd')) < 0
    call add(l:resolverNames, 'initialCwd')
  endif

  return {
        \ 'initialCwd': a:initialCwd,
        \ 'projectRootMarkers': a:projectRootMarkers,
        \ 'forbiddenProjectRoots': l:forbiddenProjectRoots,
        \ 'repository': a:projectRepository,
        \ 'resolveProjectForFile': function('s:resolveProjectForFile'),
        \ 'noninteractiveResolverNames': l:resolverNames
        \ }
endfunction

function s:isValidResolverName(index, resolverName) abort
  let l:validProjectResolverNames = ['manual', 'rootMarkers', 'initialCwd']

  return index(l:validProjectResolverNames, a:resolverName) >= 0
endfunction

function s:resolveProjectForFile(file, allowInteractive) dict abort
  let l:project = l:self.repository.findProjectContainingFile(a:file)

  if type(l:project) == type({})
    return l:project
  endif

  " see phpactor#_path
  let l:initialDirectory = fnamemodify(a:file, ':p:h')
  let l:rootDirByMarker = phpactor#fileutils#searchDirectoryUpwardByRootMarkers(
        \ l:initialDirectory,
        \ l:self.projectRootMarkers,
        \ l:self.forbiddenProjectRoots
        \ )

  let l:resolvers = {
        \ 'initialCwd': { -> l:self.initialCwd },
        \ 'manual': function('input', ['Enter file path: ', l:initialDirectory, 'file'])
        \ }

  if v:null != l:rootDirByMarker
    let l:resolvers['rootMarkers'] = { -> l:rootDirByMarker }
  endif

  if v:false is a:allowInteractive
    return s:resolveNoninteractive(l:resolvers, l:self.noninteractiveResolverNames)
  endif

  let l:choices = [
        \ {
        \ 'action': l:resolvers['initialCwd'],
        \ 'message': s:printHeader(a:file, l:self.initialCwd)
        \ },
        \ {
        \ 'action': l:resolvers['manual'],
        \ 'message': printf('manual (default "%s")', l:initialDirectory)
        \ }
        \ ]

  if v:null != l:rootDirByMarker
    let l:item = {
          \ 'action': l:resolvers['rootMarkers'],
          \ 'message': 'autodetected by root marker: '.l:rootDirByMarker
          \ }
    call add(l:choices, l:item)
  endif

  let l:choice = 0
  while index(range(1, len(l:choices)-1), l:choice) < 0
    try
      let l:choice = inputlist(map(copy(l:choices), {
            \ number, item -> (number ? printf('%d. ', number) : '') . item['message']
            \ }))
    catch /^Vim:Interrupt$/
      break
    endtry

    if l:choice == 0
      break
    endif

    redraw
  endwhile

  let l:selectedDir = l:choices[l:choice]['action']()
  redraw

  if v:null != l:selectedDir
    let l:project = phpactor#project#project#createFromRootPath(l:selectedDir)

    return l:project
  endif
endfunction

function s:resolveNoninteractive(resolvers, resolverNames) abort
  for l:resolverName in a:resolverNames
    if v:null is get(a:resolvers, l:resolverName, v:null)
      continue
    endif

    let l:primaryRootPath = a:resolvers[l:resolverName]()

    if l:primaryRootPath !=# ''
      return phpactor#project#project#createFromRootPath(l:primaryRootPath)
    endif
  endfor
endfunction

function s:printHeader(filePath, initialCwd) abort
  " heredoc are available since vim 8.1.1354
  return printf(join([
        \ 'Phpactor [RPC]',
        \ 'There is no project enabled for "%s" yet.',
        \ 'Select the way of assign the project root',
        \ 'If cancelled, "%s" will be used.'
        \], "\n"), a:filePath, a:initialCwd)
endfunction
