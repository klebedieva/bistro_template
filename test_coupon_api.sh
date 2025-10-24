#!/bin/bash

# Тестирование API промокодов
# Запуск: bash test_coupon_api.sh

BASE_URL="http://localhost"

echo "======================================"
echo "Тестирование API промокодов"
echo "======================================"
echo ""

# Цвета для вывода
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Валидный промокод
echo -e "${YELLOW}Test 1: Валидный промокод PROMO10 для заказа 30€${NC}"
echo "POST $BASE_URL/api/coupon/validate"
curl -X POST "$BASE_URL/api/coupon/validate" \
  -H "Content-Type: application/json" \
  -d '{"code":"PROMO10","orderAmount":30.00}' \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Скидка 3€ (10% от 30€)"
echo "======================================"
echo ""

# Test 2: Минимальная сумма не достигнута
echo -e "${YELLOW}Test 2: PROMO10 для заказа 15€ (минимум 20€)${NC}"
echo "POST $BASE_URL/api/coupon/validate"
curl -X POST "$BASE_URL/api/coupon/validate" \
  -H "Content-Type: application/json" \
  -d '{"code":"PROMO10","orderAmount":15.00}' \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Ошибка - минимум не достигнут"
echo "======================================"
echo ""

# Test 3: Фиксированная скидка
echo -e "${YELLOW}Test 3: Фиксированная скидка WELCOME5 для заказа 25€${NC}"
echo "POST $BASE_URL/api/coupon/validate"
curl -X POST "$BASE_URL/api/coupon/validate" \
  -H "Content-Type: application/json" \
  -d '{"code":"WELCOME5","orderAmount":25.00}' \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Скидка 5€"
echo "======================================"
echo ""

# Test 4: Промокод с максимальной скидкой
echo -e "${YELLOW}Test 4: SUMMER20 (20%, max 10€) для заказа 100€${NC}"
echo "POST $BASE_URL/api/coupon/validate"
curl -X POST "$BASE_URL/api/coupon/validate" \
  -H "Content-Type: application/json" \
  -d '{"code":"SUMMER20","orderAmount":100.00}' \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Скидка 10€ (максимум), а не 20€"
echo "======================================"
echo ""

# Test 5: Несуществующий промокод
echo -e "${YELLOW}Test 5: Несуществующий промокод${NC}"
echo "POST $BASE_URL/api/coupon/validate"
curl -X POST "$BASE_URL/api/coupon/validate" \
  -H "Content-Type: application/json" \
  -d '{"code":"INVALID123","orderAmount":30.00}' \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Ошибка 404 - промокод не найден"
echo "======================================"
echo ""

# Test 6: Просроченный промокод
echo -e "${YELLOW}Test 6: Просроченный промокод EXPIRED${NC}"
echo "POST $BASE_URL/api/coupon/validate"
curl -X POST "$BASE_URL/api/coupon/validate" \
  -H "Content-Type: application/json" \
  -d '{"code":"EXPIRED","orderAmount":30.00}' \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Ошибка - промокод истёк"
echo "======================================"
echo ""

# Test 7: Неактивный промокод
echo -e "${YELLOW}Test 7: Неактивный промокод INACTIVE${NC}"
echo "POST $BASE_URL/api/coupon/validate"
curl -X POST "$BASE_URL/api/coupon/validate" \
  -H "Content-Type: application/json" \
  -d '{"code":"INACTIVE","orderAmount":30.00}' \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Ошибка - промокод неактивен"
echo "======================================"
echo ""

# Test 8: VIP промокод
echo -e "${YELLOW}Test 8: VIP15 (15%) для заказа 100€${NC}"
echo "POST $BASE_URL/api/coupon/validate"
curl -X POST "$BASE_URL/api/coupon/validate" \
  -H "Content-Type: application/json" \
  -d '{"code":"VIP15","orderAmount":100.00}' \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Скидка 15€"
echo "======================================"
echo ""

# Test 9: SPECIAL3 без минимума
echo -e "${YELLOW}Test 9: SPECIAL3 (3€) для заказа 10€ (без минимума)${NC}"
echo "POST $BASE_URL/api/coupon/validate"
curl -X POST "$BASE_URL/api/coupon/validate" \
  -H "Content-Type: application/json" \
  -d '{"code":"SPECIAL3","orderAmount":10.00}' \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Скидка 3€"
echo "======================================"
echo ""

# Test 10: Список активных промокодов
echo -e "${YELLOW}Test 10: Получить список активных промокодов${NC}"
echo "GET $BASE_URL/api/coupon/list"
curl -X GET "$BASE_URL/api/coupon/list" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n"
echo ""
echo "Ожидаемый результат: Список всех активных промокодов"
echo "======================================"
echo ""

echo -e "${GREEN}Тестирование завершено!${NC}"

