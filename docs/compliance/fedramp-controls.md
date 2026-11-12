# FedRAMP compliance evidence

This document records how the `acquia/cohesion-dev` repository (the Site Studio Drupal module plus the `cohesion-services/dx8-gateway` API container) satisfies or inherits the Cortex "FedRAMP Compliance" scorecard controls listed below. Each section is the target of a `Compliance` link in `.acquia/.cortex/cortex.yaml`.

## NET-05.2

*Network Security (NET-05.2): Mechanisms exist to control internal system connections through authorizing internal connections of systems and documenting, for each internal connection, the interface characteristics, security requirements and the nature of the information communicated.*

**Status:** Inherited

Cluster networking, namespace isolation and network policy for the deployed `cohesion-api` service are provided by the ACE/ACN platform and applied through GitOps. This repository only declares the service's own interfaces. The container exposes a single application port, 3000, and the platform manifest routes ingress to it with TLS enabled. The Node.js process talks to the PHP-FPM twig renderer in the same container over a local unix socket (`/tmp/php-fpm.sock`), not over the network, and the socket is owned by the non-root `acquia` user. The Drupal module calls the gateway with request headers carrying the site ID, API key, organization key and a request ID (`X-Request-ID`) used to trace each connection.

**Evidence:**
- `.acquia/platform.yaml`
- `cohesion-services/dx8-gateway/Dockerfile`
- `cohesion-services/dx8-gateway/conf/etc/php/8.2/fpm/pool.d/www.conf`
- `cohesion-services/dx8-gateway/node/app/lib/twig-renderer.js`
- `src/CohesionApiClient.php`

## TPM-04.2

*Third-Party Management (TPM-04.2): Mechanisms exist to require process owners to identify the ports, protocols and other services required for the use of such services.*

**Status:** Implemented

The repository documents the services it depends on and the ports and protocols used to reach them. The Drupal module reaches the Site Studio API over HTTPS using `Drupal::httpClient()`; the default endpoint is defined in `ApiUtils::$defaultApiUrl` and may only be overridden when the site sets the `dx8_editable_api_url` setting. The gateway container listens on TCP 3000 (plus 9229, which the Dockerfile labels as a development-tool port, and 9003) and serves HTTP behind a TLS-enabled platform ingress. Runtime secrets are pulled from the platform secret store paths named in `platform.yaml`, and logs are shipped by Fluentd, configured through a Sumo Logic endpoint variable. The images are pulled from the Acquia JFrog registry, and `x-cortex-dependency` in the Cortex descriptor lists the related services `cohesion-dx8-api-proxy` and `cohesion-dx8-deploy`.

**Evidence:**
- `src/ApiUtils.php`
- `src/CohesionApiClient.php`
- `cohesion-services/dx8-gateway/Dockerfile`
- `cohesion-services/dx8-gateway/conf/etc/fluent/fluentd.conf`
- `.acquia/platform.yaml`
- `.acquia/.cortex/cortex.yaml`

## TDA-18

*Technology Development & Acquisition (TDA-18): Mechanisms exist to check the validity of information inputs.*

**Status:** Implemented

Input is checked at several layers. On the Drupal side, administration screens extend `CohesionBaseForm` and use the Drupal Form API, configuration structure is declared in `config/schema/cohesion.schema.yml`, and `Drupal\cohesion\Services\JsonXss` returns the JSON paths of layout values that fail Xss filtering, with the `bypass xss cohesion` permission controlling who may skip that check. In the gateway, request payloads are described by JSON schemas under `node/app/schemas/` and per-controller `request.schema.js` files. A `validate` controller parses submitted CSS selectors with `selector-validation.js` and reports whether they are valid, and `validate-headers.js` is the middleware slot for header checks (its checks are still marked `@todo`, so header validation is not claimed here). The PHP side includes `TwigValidator.php` for template validation, and the gateway CI pipeline runs API tests, lint and dependency checks.

**Evidence:**
- `src/Form/CohesionBaseForm.php`
- `config/schema/cohesion.schema.yml`
- `src/Services/JsonXss.php`
- `cohesion-services/dx8-gateway/node/app/schemas/input.json`
- `cohesion-services/dx8-gateway/node/app/controllers/validate/validate.js`
- `cohesion-services/dx8-gateway/node/app/lib/selector-validation.js`
- `cohesion-services/dx8-gateway/php/app/lib/TwigValidator.php`
- `.acquia/pipeline.yaml`

## VPM-05

*Vulnerability & Patch Management (VPM-05): Mechanisms exist to conduct software patching for all deployed operating systems, applications and firmware.*

**Status:** Implemented

Application dependencies are patched through Dependabot, which `.github/dependabot.yml` configures to run weekly (Fridays) for the root npm manifest, `apps`, the gateway's Node manifest and the gateway's PHP Composer manifest, with major, minor and patch updates grouped. The `automerge.yml` workflow approves and auto-squash-merges pull requests opened by `dependabot[bot]`, subject to the repository's required checks. Lockfiles (`package-lock.json`, `composer.lock`) pin the resolved versions. The gateway image is rebuilt by the pipeline, and its Dockerfile runs `apt-get upgrade` on the base layer at build time, so each rebuild picks up current OS package patches. The pipeline has a `security_scan` stage using Orca with `ignore_failures: false`. Patching of the underlying nodes, cluster and base platform is inherited from ACE/ACN.

**Evidence:**
- `.github/dependabot.yml`
- `.github/workflows/automerge.yml`
- `cohesion-services/dx8-gateway/Dockerfile`
- `cohesion-services/dx8-gateway/php/composer.lock`
- `cohesion-services/dx8-gateway/node/package-lock.json`
- `.acquia/pipeline.yaml`

## AST-04

*Has asset management for service architecture diagrams (AST-04): Mechanisms exist to maintain network architecture diagrams that: - Contain sufficient detail to assess the security of the network's architecture; - Reflect the current state of the network environment; and - Document all sensitive data flows.*

**Status:** Inherited

Network-level architecture diagrams for the deployed environment are maintained centrally for the ACE/ACN platform and cover cluster, ingress and boundary details that are not defined in this repository. What the repository shows is the service-level data flow:

1. A Drupal site running the Site Studio module sends HTTPS requests from `CohesionApiClient` to the Site Studio API (gateway), authenticated with the site's API key and organization key in request headers.
2. The gateway, a Node.js Express service on port 3000, handles the request and builds CSS and template output. For twig rendering it calls PHP-FPM inside the same container over a unix socket.
3. Results are returned to the Drupal site, which stores them in its own database and files. The React application under `apps/` is built from this repository as part of the Site Studio admin UI.
4. Logs leave the container through Fluentd. The `platform.yaml` environment definitions (dev, staging, production) and the Cortex descriptor, which declares the `in-fedramp-boundary` group, define where the service runs.

The data-flow description above should be updated whenever the gateway interfaces change.

**Evidence:**
- `cohesion-services/AGENTS.md`
- `cohesion-services/dx8-gateway/Dockerfile`
- `src/CohesionApiClient.php`
- `cohesion-services/dx8-gateway/node/app/lib/twig-renderer.js`
- `.acquia/platform.yaml`
- `.acquia/.cortex/cortex.yaml`

## TDA-04

*Has technology development and acquisition (TDA-04): Mechanisms exist to obtain, protect and distribute administrator documentation for systems that describe: - Secure configuration, installation and operation of the system; - Effective use and maintenance of security features/functions; and - Known vulnerabilities regarding configuration and use of administrative (e.g., privileged) functions.*

**Status:** Implemented

This is largely a meta control about maintaining release and administrator documentation, since deployed configuration is controlled through GitOps and covered by the CFG controls. In this repository the documentation is versioned alongside the code. `README.md` and `WORKFLOW_GUIDE.md` cover installation and development workflow, `AGENTS.md` and `cohesion-services/AGENTS.md` describe the module and the gateway service, including its compliance posture (minimal base image, non-root user) and build and deploy process, and `RELEASE_NOTES.md` records changes, bug fixes and breaking changes for each release. Pull requests must update `RELEASE_NOTES.md` and documentation per the checklist in `AGENTS.md`, and `CONTRIBUTING.md` states the contribution rules. Deployment configuration is in `.acquia/platform.yaml` and `.acquia/pipeline.yaml`, under version control and review.

**Evidence:**
- `README.md`
- `WORKFLOW_GUIDE.md`
- `AGENTS.md`
- `cohesion-services/AGENTS.md`
- `RELEASE_NOTES.md`
- `CONTRIBUTING.md`
- `.acquia/platform.yaml`
