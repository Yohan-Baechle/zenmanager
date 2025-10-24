export default function Loader() {
    return (
        <div className="flex flex-row gap-2 justify-center items-center h-full w-full">
            <div className="w-4 h-4 rounded-full bg-[var(--c5)] animate-bounce"></div>
            <div className="w-4 h-4 rounded-full bg-[var(--c5)] animate-bounce [animation-delay:-.3s]"></div>
            <div className="w-4 h-4 rounded-full bg-[var(--c5)] animate-bounce [animation-delay:-.5s]"></div>
        </div>
    )
}
