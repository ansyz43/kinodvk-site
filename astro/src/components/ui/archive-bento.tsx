"use client";

import { BentoGrid, Film, Camera, FileText, Sparkles, Clapperboard, Award, type BentoItem } from "./bento-grid";

interface Props { year: "2024" | "2025" | "2026" }

const data: Record<string, BentoItem[]> = {
  "2024": [
    { title: "Сто лет тому вперёд", meta: "Полный метр", description: "Алексей Нужный · 2024. Якутия / Приморье.", icon: <Film className="w-4 h-4 text-amber-600" />, status: "Winner", tags: ["Победитель", "Полный метр"], colSpan: 2, hasPersistentHover: true, image: "/assets/gallery/2024/3K6A1474_resized.jpg" },
    { title: "Айта", meta: "Режиссура", description: "Степан Бурнашёв · 2022. Якутия.", icon: <Clapperboard className="w-4 h-4 text-amber-600" />, status: "Winner", tags: ["Режиссура"], image: "/assets/gallery/2024/3K6A2040_resized.jpg" },
    { title: "Не одна дома", meta: "Сценарий", description: "Радда Новикова · 2023. Сахалин.", icon: <FileText className="w-4 h-4 text-slate-500" />, tags: ["Сценарий"], colSpan: 2, image: "/assets/gallery/2024/3K6A2829_resized.jpg" },
    { title: "Куба, Куба", meta: "Дебют", description: "Кирилл Кравченко · 2024. Приморье.", icon: <Sparkles className="w-4 h-4 text-slate-500" />, tags: ["Дебют"], image: "/assets/gallery/2024/3K6A1134_resized.jpg" },
    { title: "Тойук", meta: "Короткий метр", description: "Виктор Аммосов · 2023. Якутия.", icon: <Camera className="w-4 h-4 text-slate-500" />, tags: ["Короткий метр"], image: "/assets/gallery/2024/3K6A3214_resized.jpg" },
    { title: "Дмитрий Давыденко", meta: "За вклад в развитие", description: "Продюсер, Якутск.", icon: <Award className="w-4 h-4 text-amber-600" />, status: "Winner", tags: ["Спец. приз"], colSpan: 2, image: "/assets/gallery/2024/3K6A3186_resized.jpg" },
  ],
  "2025": [
    { title: "Огонь", meta: "Полный метр", description: "Алексей Нужный · 2024. Камчатка.", icon: <Film className="w-4 h-4 text-amber-600" />, status: "Winner", tags: ["Победитель"], colSpan: 2, hasPersistentHover: true, image: "/assets/gallery/2025/LX3A5728_resized.jpg" },
    { title: "Чёрная молния возвращается", meta: "Режиссура", description: "Дмитрий Давыдов · 2024. Якутия.", icon: <Clapperboard className="w-4 h-4 text-amber-600" />, status: "Winner", tags: ["Режиссура"], image: "/assets/gallery/2025/LX3A5746_resized.jpg" },
    { title: "Бэлэх", meta: "Сценарий", description: "Степан Бурнашёв · 2024. Якутия.", icon: <FileText className="w-4 h-4 text-slate-500" />, tags: ["Сценарий"], colSpan: 2, image: "/assets/gallery/2025/LX3A6180_resized.jpg" },
    { title: "Триозёрск", meta: "Дебют", description: "Сергей Кузнецов · 2024. Хабаровск.", icon: <Sparkles className="w-4 h-4 text-slate-500" />, tags: ["Дебют"], image: "/assets/gallery/2025/LX3A6615_resized.jpg" },
    { title: "Куда мы едем", meta: "Короткий метр", description: "Татьяна Эверстова · 2024. Якутия.", icon: <Camera className="w-4 h-4 text-slate-500" />, tags: ["Короткий метр"], image: "/assets/gallery/2025/LX3A5732_resized.jpg" },
    { title: "Алёна Шумакова", meta: "За вклад", description: "Программный директор, Бурятия.", icon: <Award className="w-4 h-4 text-amber-600" />, status: "Winner", tags: ["Спец. приз"], colSpan: 2, image: "/assets/gallery/2025/LX3A5740_resized.jpg" },
  ],
  "2026": [
    { title: "Полный метр", meta: "Шорт-лист", description: "Откроется в июне 2026, после приёма заявок.", icon: <Film className="w-4 h-4 text-slate-500" />, status: "Pending", tags: ["Шорт-лист"], colSpan: 2 },
    { title: "Режиссура", meta: "Шорт-лист", description: "Откроется в июне 2026.", icon: <Clapperboard className="w-4 h-4 text-slate-500" />, status: "Pending" },
    { title: "Сценарий", meta: "Шорт-лист", description: "Откроется в июне 2026.", icon: <FileText className="w-4 h-4 text-slate-500" />, status: "Pending" },
    { title: "Дебют", meta: "Шорт-лист", description: "Откроется в июне 2026.", icon: <Sparkles className="w-4 h-4 text-slate-500" />, status: "Pending" },
    { title: "Короткий метр", meta: "Шорт-лист", description: "Откроется в июне 2026.", icon: <Camera className="w-4 h-4 text-slate-500" />, status: "Pending" },
    { title: "Арктика", meta: "Новая номинация", description: "Фильмы о Крайнем Севере и Заполярье.", icon: <Award className="w-4 h-4 text-amber-600" />, status: "Новая", tags: ["Арктика"], colSpan: 2, hasPersistentHover: true },
  ],
};

export default function ArchiveBento({ year }: Props) {
  return <BentoGrid items={data[year] || []} />;
}
