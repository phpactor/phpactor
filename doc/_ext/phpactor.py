from docutils import nodes
from docutils.parsers.rst import Directive

gh_repo_tpl = """\
{name}
"""

class GitHubRepoDirective(Directive):
    """Directive for Github Repositories."""
    required_arguments = 1
    optional_arguments = 0
    final_argument_whitespace = False
    has_content = False

    def run(self):
        repo = self.arguments[0]
        env = self.state.document.settings.env

        repo_link = nodes.reference('', repo, refuri='https://github.com/' + repo)

        title = nodes.paragraph(classes=['github-link'])
        github_icon = nodes.emphasis(classes=['fa fa-github fa-lg'])
        title += github_icon,
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
