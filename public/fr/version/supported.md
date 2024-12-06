# Plan de soutien

| Branche | Version de PHP | Date de début | Date limite de soutien actif | Date limite de maintenance de sécurité |
|-----------------------------------------------------------------|-----------|------------|------------|------------|
| [v4.8.x](https://github.com/swoole/swoole-src/tree/4.8.x)  | 7.2 - 8.2 | 2021-10-14 | 2023-10-14 | 2024-06-30 |
| [v5.0.x](https://github.com/swoole/swoole-src/tree/5.0.x)       | 8.0 - 8.2 | 2022-01-20 | 2023-01-20 | 2023-07-20 |
| [v5.1.x](https://github.com/swoole/swoole-src/tree/master)      | 8.0 - 8.2 | 2023-09-30 | 2025-09-30 | 2026-09-30 |
| [v6.0.x](https://github.com/swoole/swoole-src/tree/master)      | 8.1 - 8.3 | 2024-06-23 | 2026-06-23 | 2027-06-23 |

| Soutien actif | Recevoir un soutien actif de l'équipe de développement officielle, les erreurs et problèmes de sécurité signalés seront immédiatement corrigés et publiés sous forme de versions officielles selon le processus habituel. |
| -------- | ---------------------------------------------------------------------------------------------- |
| Maintenance de sécurité | Seule la correction de problèmes de sécurité critiques est prise en charge, et des versions officielles ne sont publiées que si nécessaire |


## Branches no longer supported

!> Ces versions ne sont plus soutenues officiellement, les utilisateurs qui utilisent encore ces versions devraient passer à une version plus récente dès que possible, car ils pourraient rencontrer des vulnérabilités de sécurité non corrigées.



- `v1.x` (juillet 2012 ~ mai 2018)

- `v2.x` (décembre 2016 ~ mai 2018)

- `v3.x` (déprécié)

- `v4.0.x`, `v4.1.x`, `v4.2.x`, `v4.3.x` (juin 2018 ~ décembre 2019)

- `v4.4.x` (avril 2019 ~ avril 2020)

- `v4.5.x` (décembre 2019 ~ janvier 2021)
- `v4.6.x`, `v4.7.x` (janvier 2021 ~ décembre 2021)




## Caractéristiques des versions

- `v1.x`：Mode de callback asynchrone.

- `v2.x`：Coroutines à une pile basées sur `setjmp/longjmp`, le mécanisme de base est toujours un callback asynchrone, et le changement de pile PHP est déclenché après l'événement du callback.

- `v4.0-v4.3`：Coroutines à double pile basées sur `boost context asm`, le noyau a réalisé une coroutineisation complète, réalisant un décalage de coroutines basé sur `EventLoop`.

- `v4.4-v4.8`：Réalisé le `hook coroutine runtime`, remplaçant automatiquement les fonctions bloquantes synchrones intégrées PHP par des modes asynchrones non bloquants de coroutines, permettant aux coroutines Swoole d'être compatibles avec la plupart des bibliothèques PHP.

- `v5.0`：Coroutineisation complète, suppression des modules non coroutines ; typage fort, suppression de nombreux fardeaux historiques ; offre un nouveau mode de fonctionnement `swoole-cli`.
- `v5.1` : Prise en charge de la coroutineisation pour `pdo_pgsql`, `pdo_oci`, `pdo_odbc`, `pdo_sqlite`, améliorant les performances du serveur `Http\Server`.
- `v6.0` : Prise en charge du mode multithread.
