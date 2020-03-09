""
" @section Mappings
"
" Phpactor does not assume any mappings automatically, the following mappings
" are available for you to copy: >
"
"   augroup PhpactorMappings
"     au!
"     au FileType php nmap <Leader>u :PhpactorImportClass<CR>
"     au FileType php nmap <Leader>mm :PhpactorContextMenu<CR>
"     au FileType php nmap <Leader>nn :PhpactorNavigate<CR>
"     au FileType php nmap <Leader>oo :PhpactorGotoDefinition<CR>
"     au FileType php nmap <Leader>oh :PhpactorGotoDefinitionHsplit<CR>
"     au FileType php nmap <Leader>ov :PhpactorGotoDefinitionVsplit<CR>
"     au FileType php nmap <Leader>ot :PhpactorGotoDefinitionTab<CR>
"     au FileType php nmap <Leader>K :PhpactorHover<CR>
"     au FileType php nmap <Leader>tt :PhpactorTransform<CR>
"     au FileType php nmap <Leader>cc :PhpactorClassNew<CR>
"     au FileType php nmap <silent><Leader>ee :PhpactorExtractExpression<CR>
"     au FileType php vmap <silent><Leader>ee :<C-u>PhpactorExtractExpression<CR>
"     au FileType php vmap <silent><Leader>em :<C-u>PhpactorExtractMethod<CR>
"   augroup END
" <
"
