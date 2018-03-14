FROM node:8

# UID and GID of the compiled files, will be set with chown
ARG ASSET_USER=node
ARG ASSET_GROUP=node

# Create target directories for compiled assets
RUN mkdir -p /app/skins/10h16/web/_js /app/skins/cat17/web

# Install node.js dependencies - should be very stable part of the build
COPY skins/10h16/package*.json /app/skins/10h16/
COPY skins/cat17/package*.json /app/skins/cat17/
RUN cd /app/skins/10h16 && npm install && cd /app/skins/cat17 && npm install

# Install app code, ordered by probability of changes to skip intermediate steps more often
COPY package.json /app/package.json
COPY npm_skins.js /app/npm_skins.js
COPY skins/cat17/gulpfile.js /app/skins/cat17
COPY skins/cat17/src /app/skins/cat17/src
COPY skins/10h16/js /app/skins/10h16/js

WORKDIR /app

# Initialize our own node_modules
RUN npm install --ignore-scripts --unsafe-perm

# Build assets
RUN npm run build-assets
RUN chown -R $ASSET_USER:$ASSET_GROUP /app/skins/10h16/web/_js && chown -R $ASSET_USER:$ASSET_GROUP /app/skins/cat17/web

# By default, the container copies the built assets.
# When a volume is mounted to /app/web, the assets will be copied there
CMD npm run copy-assets