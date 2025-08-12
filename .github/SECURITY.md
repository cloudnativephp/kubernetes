# Security Policy

We take the security of this project seriously and appreciate responsible disclosures.

## Supported Versions
We generally support the latest minor release line. Security fixes will target the default branch and may be backported at maintainers’ discretion.

## Reporting a Vulnerability
- Do not publicly disclose the issue.
- Use GitHub Security Advisories (preferred): open a private security advisory in the repository so we can coordinate a fix and disclosure.
- If you cannot use advisories, email the maintainer listed in `composer.json` authors.

Please include:
- Affected versions and environment details (PHP version, OS)
- Reproduction steps or a proof of concept
- Potential impact and any known mitigations

You will receive an acknowledgment within 72 hours. We aim to provide an initial assessment and next steps promptly. Once a fix is available, we will coordinate CVE requests if applicable and publish a new release.

## Dependencies
This library depends on third‑party packages (e.g., Guzzle, Symfony components). We rely on automated tooling to stay current. If a dependent package has a vulnerability that affects this project, please note it in your report.

## Public Disclosure
After a fix is released, we will publish a security advisory summarizing the issue, impact, fixed versions, and mitigations.
