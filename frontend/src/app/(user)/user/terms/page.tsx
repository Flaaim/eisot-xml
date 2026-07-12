import { Button } from "@/components/ui/button";
import Link from "next/link";
import { ArrowLeft } from "lucide-react";

export default function TermsPage() {
  return (
    <div className="mx-auto max-w-4xl space-y-6 p-4 md:p-8">
      <div className="mb-6">
        <Button
          variant="ghost"
          size="sm"
          className="pl-0 text-muted-foreground hover:bg-transparent hover:text-gray-900"
        >
          <Link href="/user/subscription" className="inline-flex items-center">
            <ArrowLeft className="mr-2 size-4" />
            <span>Назад</span>
          </Link>
        </Button>
      </div>
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Условия использования</h1>
        <h2 className="mt-3 text-2xl font-bold tracking-tight">1. Оплата и доступ к сервису</h2>
        <p className="mt-3 text-sm text-muted-foreground">
          Сайт подключен к платежному сервису ЮKassa. Вы можете оплатить доступ к функциям сервиса
          (подписку) банковскими картами: Visa, Mastercard, Maestro, МИР, а также системами SberPay,
          Tinkoff Pay и другими доступными в ЮKassa способами.
        </p>
        <p className="mt-3 text-sm text-muted-foreground">
          После подтверждения платежа подписка активируется автоматически для вашего аккаунта (User
          ID) . Доступ предоставляется к функционалу формирования XML-файлов согласно актуальной
          XSD-схеме (версии 1.0.9 и выше) для передачи данных в ЕИСОТ Минтруда
        </p>
        <h3 className="text-1xl mt-3 font-bold tracking-tight">Тарифные планы:</h3>
        <ol className="mt-3 list-inside list-decimal space-y-1 text-sm text-muted-foreground">
          <li>
            <b>Базовый:</b> позволяет работать и формировать реестры для 1 (одной) компании
            (активной или в архиве)
          </li>
          <li>
            <b>Расширенный:</b> предоставляет возможность работы с неограниченным количеством
            компаний в рамках одного аккаунта
          </li>
        </ol>
        <p className="mt-3 text-sm text-muted-foreground">
          Срок действия подписки определяется выбранным периодом при оплате. По окончании срока
          действия доступ к выгрузке XML-файлов ограничивается до момента продления.
        </p>
        <h2 className="mt-3 text-2xl font-bold tracking-tight">2. Гарантии безопасности</h2>
        <p className="mt-3 text-sm text-muted-foreground">
          Платежи обрабатываются в соответствии со стандартом PCI DSS. Данные карты передаются в
          зашифрованном виде (SSL/TLS) и не сохраняются на нашем сервере. ЮKassa не передает нам
          полные реквизиты вашей карты. Для дополнительной безопасности используется протокол 3D
          Secure.
        </p>
        <h2 className="mt-3 text-2xl font-bold tracking-tight">3. Использование сервиса</h2>
        <p className="mt-3 text-sm text-muted-foreground">
          Сервис eisot-xml.ru предоставляет инструменты для автоматизации формирования файлов
          RegistrySet. Пользователь несет ответственность за достоверность вводимых данных (ИНН,
          СНИЛС, номера протоколов). Сервис обеспечивает проверку контрольных сумм ИНН и СНИЛС для
          минимизации ошибок при импорте в систему ЕИСОТ
        </p>
        <p className="mt-3 text-sm text-muted-foreground">
          Сформированные файлы и доступ к личному кабинету предназначены только для использования
          владельцем аккаунта.
        </p>
        <h2 className="mt-3 text-2xl font-bold tracking-tight">4. Возврат средств</h2>
        <p className="mt-3 text-sm text-muted-foreground">
          Возврат денежных средств возможен в течение 24 часов после первой активации подписки, если
          технические возможности сервиса не соответствуют заявленным (например, формируемый
          XML-файл не проходит валидацию по официальной схеме Минтруда при условии корректности
          введенных пользователем данных)
        </p>
        <p className="mt-3 text-sm text-muted-foreground">
          Для оформления возврата напишите на электронную почту или в{" "}
          <Link className="link" href="https://t.me/flaaim" target="_blank">
            Телеграм
          </Link>{" "}
          с указанием e-mail аккаунта и номера заказа. Средства вернутся на ту же карту в течение
          5–10 рабочих дней
        </p>
        <h2 className="mt-3 text-2xl font-bold tracking-tight">5. Конфиденциальность</h2>
        <p className="mt-3 text-sm text-muted-foreground">
          Мы соблюдаем принципы конфиденциальности и не передаем ваши данные (email, ИНН компаний,
          списки работников) третьим лицам, за исключением случаев, предусмотренных
          законодательством РФ. Данные хранятся в защищенной базе данных для обеспечения работы
          вашего личного кабинета и истории выгрузок
        </p>
        <h2 className="mt-3 text-2xl font-bold tracking-tight">6. Контакты</h2>
        <p className="mt-3 text-sm text-muted-foreground">
          По вопросам оплаты, активации подписки и технической поддержки: 📧 Email: flaeim@gmail.com
          🌐 Сайт: eisot-xml.ru
        </p>
      </div>
    </div>
  );
}
