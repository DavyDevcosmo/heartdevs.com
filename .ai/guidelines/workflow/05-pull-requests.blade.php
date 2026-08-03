@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp

# Pull Requests

When creating or updating a GitHub pull request, agents MUST use the repository PR template at `.github/pull_request_template.md` as the source of truth for the PR body.

## Required behavior

- Fill the PR body using the template sections: `Contexto`, `Alterações`, `Plano de Testes`, `Evidências` and `Issues Relacionadas`.
- Preserve the template structure and headings instead of writing a free-form PR description.
- Populate the checklist in `Plano de Testes` with the validation steps that were actually run.
- Include the relevant validation commands from the project workflow, especially `make check` and `make test`, when applicable.
- Add issue references in the `Issues Relacionadas` section using the repository's expected format (for example: `Closes #123`).
- If the change has no visible UI impact, keep the `Evidências` section concise or leave it empty rather than inventing screenshots.

## Expectations for agents

- Do not create a PR body from scratch when the template exists.
- Adapt the template content to the specific change, but do not remove required sections unless the repository template already says they are optional.
- Prefer clear, concise Portuguese when the repository and existing templates are written in Portuguese.
- If the PR is for a change that affects behavior, include enough detail for reviewers to understand the impact, the validation performed, and the related issues.
