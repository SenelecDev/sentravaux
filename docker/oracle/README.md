# Oracle Instant Client (extension oci8)

Si le téléchargement automatique échoue pendant le build Docker, placez ces fichiers ici :

- `instantclient-basic.zip` (Instant Client Basic 19.x ou 21.x)
- `instantclient-sdk.zip` (SDK, requis pour compiler oci8)

Téléchargement : https://www.oracle.com/database/technologies/instant-client/linux-x86-64-downloads.html

**Alternative sur CentOS 7** : si les autres applications ont déjà le client Oracle sur l'hôte :

```yaml
# docker-compose.override.yml
services:
  app:
    volumes:
      - /usr/lib/oracle/19/client64/lib:/opt/oracle/instantclient:ro
    environment:
      LD_LIBRARY_PATH: /opt/oracle/instantclient
```

Puis reconstruire sans INSTALL_OCI8 si oci8 est compilé avec le chemin monté (rebuild requis).
