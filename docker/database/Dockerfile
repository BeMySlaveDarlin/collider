FROM postgres:15-alpine

ARG POSTGRES_DB=collider_db
ARG POSTGRES_USER=collider_user
ARG POSTGRES_PASSWORD=collider_secret

RUN apk add --no-cache \
    curl \
    postgresql-contrib

COPY init/ /docker-entrypoint-initdb.d/
COPY postgresql.conf /etc/postgresql/postgresql.conf
COPY pg_hba.conf /etc/postgresql/pg_hba.conf

RUN mkdir -p /var/log/postgresql \
    && chown -R postgres:postgres /var/log/postgresql \
    && chmod 755 /var/log/postgresql

EXPOSE 5432

ENV POSTGRES_DB=${POSTGRES_DB}
ENV POSTGRES_USER=${POSTGRES_USER}
ENV POSTGRES_PASSWORD=${POSTGRES_PASSWORD}

CMD ["postgres", "-c", "config_file=/etc/postgresql/postgresql.conf"]
