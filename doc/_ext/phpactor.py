from docutils import nodes
from docutils.parsers.rst import Directive
from docutils.parsers.rst import directives


class GitHubRepoDirective(Directive):
    """Directive for GitHub Repositories."""
    required_arguments = 1
    optional_arguments = 0
    final_argument_whitespace = False
    has_content = False

    def run(self):
        repo = self.arguments[0]
        env = self.state.document.settings.env

        repo_link = nodes.reference('', repo, refuri='https://github.com/' + repo)

        title = nodes.paragraph(classes=['github-link'])
        
        github_icon = nodes.image(uri=directives.uri("/images/github.svg"),width="15px",height="15px")
        title += github_icon,
        title += nodes.emphasis(strong=True,text=' GitHub:')
        title += nodes.inline(text=' ')
        title += repo_link,


        new_nodes = [title]

        return new_nodes

def setup(app):
    app.add_directive("github-link", GitHubRepoDirective)

    return {
        'version': '0.1',
        'parallel_read_safe': True,
        'parallel_write_safe': True,
    }
