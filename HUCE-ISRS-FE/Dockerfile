# ────────────────────────────────────────────────────────────
# Stage 1: Build (Node 20 Alpine)
# ────────────────────────────────────────────────────────────
FROM node:20-alpine AS builder

WORKDIR /app

# Cài deps trước (tận dụng layer cache)
COPY package.json package-lock.json ./
RUN npm ci

# Copy toàn bộ source và build
COPY . .
# VITE_API_BASE_URL được truyền qua build-arg khi CI/CD build image
ARG VITE_API_BASE_URL=http://localhost:8000/api
ENV VITE_API_BASE_URL=$VITE_API_BASE_URL

RUN npm run build

# ────────────────────────────────────────────────────────────
# Stage 2: Serve static (Nginx Alpine)
# ────────────────────────────────────────────────────────────
FROM nginx:1.27-alpine AS runner

# Xóa config mặc định
RUN rm /etc/nginx/conf.d/default.conf

# Copy config tùy chỉnh
COPY nginx.conf /etc/nginx/conf.d/app.conf

# Copy build output từ stage 1
COPY --from=builder /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
