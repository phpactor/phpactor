""
" @section Completion
"
" You will need to explicitly configure Phpactor to provide completion
" capabilities.
"
" @subsection Omni-Completion
"
" Use VIMs native omni-completion (|compl-omni|)
"
" Enable omni-completion for PHP files: >
"
"   autocmd FileType php setlocal omnifunc=phpactor#Complete
"
" For case sensitive searching see @setting(g:phpactorCompletionIgnoreCase)
"
" @subsection NCM2
"
" Nvim Completion Manager is a completion manager for Neovim. 
"
" Install the integration plugin to get started: https://github.com/phpactor/ncm2-phpactor
"
" @subsection Deoplete
"
" Deoplete is another completion plugin. 
"
" Install the Deoplete Phpactor
" integration to get started: https://github.com/kristijanhusak/deoplete-phpactor
