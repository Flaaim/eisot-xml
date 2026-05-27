import type {Metadata} from "next";

export const metadata: Metadata = {
  title: "Тесты ростехнадзора",
  description: "Описание страницы",
};


export default function Home() {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center bg-gray-100">
      <h1 className="text-4xl font-extrabold text-gray-600 sm:text-5xl">Привет!</h1>
    </div>
  );
}
