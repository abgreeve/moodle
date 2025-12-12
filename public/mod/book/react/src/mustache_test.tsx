import { React } from "@moodle/core/react";
import { withProfiler } from "@moodle/core/profiler";

interface MustacheTestProps {
  title?: string;
  message?: string;
}

function MustacheTest({
  title = "MustacheTest Component",
  message = "Hello!",
}: MustacheTestProps) {
  const [count, setCount] = React.useState(0);

  return (
    <div className="alert alert-success">
      <h4>{title}</h4>
      <p>{message}</p>
      <button className="btn btn-primary" onClick={() => setCount(count + 1)}>
        Count: {count}
      </button>
    </div>
  );
}

// Export with Profiler wrapper.
export default withProfiler(MustacheTest, "MustacheTest");
