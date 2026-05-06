# Security Notes

## Application Controls

- Security headers are applied to all web responses.
- Form submissions use CSRF tokens.
- Session cookies use `HttpOnly`, `SameSite=Lax`, and secure cookies when HTTPS is detected.
- User input is normalized before scoring and persistence.
- Database writes use PDO prepared statements.
- JSON fields are validated by MySQL checks.
- Database connection failures fall back to catalog-only operation.

## Evidence Security Controls

Production deployments should add:

- Authentication and role-based access.
- Encryption for case records, manifests, artifact notes, and exports.
- Immutable chain-of-custody event storage.
- Audit logging for case access and report generation.
- Segregation between lab data, real case data, and demonstration data.
- Retention and disposal policies aligned with legal requirements.

## Privacy Handling

Humanoid robots and companion apps can hold sensitive household and personal data, including names, relationships, photos, reminders, call records, media, and usage patterns. Handle all evidence using need-to-know access, minimization, and report redaction.

