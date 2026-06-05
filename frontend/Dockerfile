FROM node:22-alpine

WORKDIR /app

# Copy dependency manifests first for better layer caching
COPY package.json package-lock.json ./

# Install dependencies inside the image (Linux-compiled binaries)
RUN npm install

# Source code is mounted at runtime via docker-compose volume.
# The node_modules installed above will be shadowed by an anonymous
# volume in docker-compose, preserving these Linux-compiled modules.
CMD ["npm", "run", "dev"]
