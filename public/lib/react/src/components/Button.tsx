import React, {useState} from "react";

type ButtonType = "button" | "submit" | "reset";

export interface ButtonProps
  extends Omit<
    React.ButtonHTMLAttributes<HTMLButtonElement>,
    "type" | "onClick"
  > {
  label?: string;
  type?: ButtonType;
  loading?: boolean;
  onClick?: (event: React.MouseEvent<HTMLButtonElement>) => void;
}

/**
 * Simple button for use with the Mustache React helper.
 */
export default function Button(props: ButtonProps): React.ReactElement {
  const {
    label,
    type = "button",
    disabled = false,
    loading = false,
    onClick,
    className = "",
    ...rest
  } = props;

  // A loading button behaves like a disabled button.
  const isDisabled = disabled || loading;

  // Pass-through for the rest of the attributes.
  const domProps = rest;

  // For profiling purposes.
  const [_count, setCount] = useState<number>(0);

  const handleClick = (event: React.MouseEvent<HTMLButtonElement>) => {
    // If button shouldn't be interactive, prevent the click.
    if (isDisabled) {
      event.preventDefault();
      return;
    }

    setCount((c) => c + 1); // Increment click count for debugging/profiler (devtools).

    if (onClick) {
      onClick(event);
    }
  };

  return (
    <button
      type={type}
      disabled={isDisabled}
      onClick={handleClick}
      className={className}
      {...domProps}
    >
      {/* When loading, show a lightweight “…’’ suffix or fallback text */}
      {loading ? (label ? `${label}…` : "Loading…") : label}
    </button>
  );
}
