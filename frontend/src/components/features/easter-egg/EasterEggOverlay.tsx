import { useEffect } from "react";
import confetti from "canvas-confetti";
import easterEggImage from "../../../assets/images/easter-egg.jpg";

interface EasterEggOverlayProps {
  onClose: () => void;
}

export default function EasterEggOverlay({ onClose }: EasterEggOverlayProps) {
  useEffect(() => {
    const duration = 5 * 1000;
    const animationEnd = Date.now() + duration;
    const defaults = {
      startVelocity: 30,
      spread: 360,
      ticks: 60,
      zIndex: 9999,
    };

    const randomInRange = (min: number, max: number) =>
      Math.random() * (max - min) + min;

    const interval = window.setInterval(() => {
      const timeLeft = animationEnd - Date.now();

      if (timeLeft <= 0) {
        return clearInterval(interval);
      }

      const particleCount = 50 * (timeLeft / duration);
      confetti({
        ...defaults,
        particleCount,
        origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
      });
      confetti({
        ...defaults,
        particleCount,
        origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
      });
    }, 250);

    return () => clearInterval(interval);
  }, []);

  return (
    <div
      className="fixed inset-0 z-[9998] flex items-center justify-center bg-black/80 cursor-pointer"
      onClick={onClose}
    >
      <div className="text-center animate-bounce">
        <div className="text-8xl mb-8">🎉</div>
        <h1 className="text-4xl font-bold text-white mb-4">
          Bravo, tu as trouvé le secret !
        </h1>
        <p className="text-xl text-gray-300 mb-4">
          Tu es officiellement un.e archéologue du code.
        </p>
        <img
          src={easterEggImage}
          alt="Easter Egg"
          className="max-w-sm max-h-64 mx-auto rounded-2xl border-4 border-[var(--c4)] shadow-2xl"
        />
        <p className="text-sm text-gray-500 mt-8">
          Clique n'importe où pour fermer
        </p>
      </div>
    </div>
  );
}
